<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Log\LoggerInterface;
use Swoole\Server;
use Swoole\Server\Task;
use Symfony\Component\Messenger\Envelope;

#[Singleton]
readonly class ServerTaskHandlerDispatcher
{
    public function __construct(
        private LoggerInterface $logger,
        private ServerTaskHandlerCollection $serverTaskHandlerCollection,
    ) {}

    public function onFinish(Server $server, int $taskId): void
    {
        $this->logger->debug(sprintf('swoole_task_finish(%d)', $taskId));
    }

    public function onTask(Server $server, Task $task): void
    {
        /**
         * @var int $task->id
         * @var int $task->worker_id
         */
        $this->logger->debug(sprintf(
            'swoole_task(%d, %d)',
            $task->id,
            $task->worker_id,
        ));

        try {
            if ($task->data instanceof Envelope) {
                $this->dispatchMessage($task->data->getMessage());
            } else {
                $this->logger->warning('swoole_task_error("unhandled message - not an Envelope")');
            }
        } finally {
            $task->finish(null);
        }
    }

    private function dispatchMessage(object $message): void
    {
        $handlers = $this
            ->serverTaskHandlerCollection
            ->serverTaskeHandler
            ->get($message::class, null)
        ;

        if (empty($handlers)) {
            return;
        }

        foreach ($handlers as $handler) {
            $handler->handleServerTask($message);
        }
    }
}
