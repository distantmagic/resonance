<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\RubixMLBackend;

use Distantmagic\Resonance\SerializerInterface;
use Rubix\ML\Backends\Backend;
use Rubix\ML\Backends\Tasks\Task;
use RuntimeException;
use Swoole\Atomic;
use Swoole\Coroutine\Socket;
use Swoole\Process;

use function Distantmagic\Resonance\helpers\coroutineMustRun;

class Swoole implements Backend
{
    /**
     * The queue of tasks to be processed in parallel.
     *
     * @var list<callable():mixed> $queue
     */
    protected array $queue = [];

    private int $cpus;

    public function __construct(
        private SerializerInterface $serializer,
    ) {
        $this->cpus = swoole_cpu_num();
    }

    /**
     * Return the string representation of the object.
     *
     * @internal
     */
    public function __toString(): string
    {
        return 'Swoole';
    }

    /**
     * @param callable(mixed,mixed):void $after
     * @param mixed                      $context explicitly mixed for typechecks
     */
    public function enqueue(Task $task, ?callable $after = null, $context = null): void
    {
        array_push($this->queue, static function () use ($task, $after, $context): mixed {
            /**
             * @var mixed explicitly mixed for typechecks
             */
            $result = $task();

            if (is_callable($after)) {
                $after($result, $context);
            }

            return $result;
        });
    }

    /**
     * Flush the queue
     */
    public function flush(): void
    {
        $this->queue = [];
    }

    /**
     * Process the queue and return the results.
     *
     * @internal
     *
     * @return list<mixed>
     */
    public function process(): array
    {
        /**
         * @var list<mixed> $results
         */
        $results = [];

        $maxMessageLength = new Atomic(0);
        $workerProcesses = [];

        foreach ($this->queue as $queueItem) {
            $workerProcess = new Process(
                function (Process $worker) use ($maxMessageLength, $queueItem) {
                    $serialized = $this->serializer->serialize($queueItem());

                    $serializedLength = strlen($serialized);
                    $currentMaxSerializedLength = $maxMessageLength->get();

                    if ($serializedLength > $currentMaxSerializedLength) {
                        $maxMessageLength->set($serializedLength);
                    }

                    /**
                     * @var false|Socket $socket
                     */
                    $socket = $worker->exportSocket();

                    if (!$socket) {
                        throw new RuntimeException('Failed to export socket');
                    }

                    $socket->send($serialized);
                },
                // redirect_stdin_and_stdout
                false,
                // pipe_type
                SOCK_DGRAM,
                // enable_coroutine
                true,
            );

            $workerProcess->setBlocking(false);
            $workerProcess->start();

            array_push($workerProcesses, $workerProcess);
        }

        coroutineMustRun(function () use ($maxMessageLength, &$results, $workerProcesses) {
            foreach ($workerProcesses as $workerProcess) {
                $status = $workerProcess->wait();

                if (0 !== $status['code']) {
                    throw new RuntimeException('Worker process exited with an error');
                }

                /**
                 * @var false|Socket $socket
                 */
                $socket = $workerProcess->exportSocket();

                if (!$socket) {
                    throw new RuntimeException('Failed to export socket');
                }

                if ($socket->isClosed()) {
                    throw new RuntimeException('Coroutine socket is closed');
                }

                $maxMessageLengthValue = $maxMessageLength->get();

                $receivedData = $socket->recv($maxMessageLengthValue);

                if (!is_string($receivedData)) {
                    throw new RuntimeException('Received data is not a string');
                }

                /**
                 * @var mixed explicitly mixed for typechecks
                 */
                $results[] = $this->serializer->unserialize($receivedData);
            }
        });

        /**
         * @psalm-suppress UnevaluatedCode psalm doesn't handle async well
         */
        return $results;
    }
}
