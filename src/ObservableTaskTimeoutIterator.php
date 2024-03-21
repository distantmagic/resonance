<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Generator;
use IteratorAggregate;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * @template-implements IteratorAggregate<ObservableTaskStatusUpdate>
 */
readonly class ObservableTaskTimeoutIterator implements IteratorAggregate
{
    /**
     * @var Closure():Generator<ObservableTaskStatusUpdate>
     */
    private Closure $iterableTask;

    /**
     * @param callable():Generator<ObservableTaskStatusUpdate> $iterableTask
     */
    public function __construct(
        callable $iterableTask,
        private float $inactivityTimeout,
    ) {
        $this->iterableTask = Closure::fromCallable($iterableTask);
    }

    /**
     * @return SwooleChannelIterator<ObservableTaskStatusUpdate>
     */
    public function __invoke(): SwooleChannelIterator
    {
        return $this->getIterator();
    }

    /**
     * @psalm-suppress UnusedVariable $generatorCoroutineId is used, just asynchronously
     *
     * @return SwooleChannelIterator<ObservableTaskStatusUpdate>
     */
    public function getIterator(): SwooleChannelIterator
    {
        /**
         * @var null|int $generatorCoroutineId
         */
        $generatorCoroutineId = null;

        $channel = new Channel(1);

        $swooleTimeout = new SwooleTimeout(static function () use (&$generatorCoroutineId) {
            if (is_int($generatorCoroutineId)) {
                Coroutine::cancel($generatorCoroutineId);
            }
        });

        $generatorCoroutineId = SwooleCoroutineHelper::mustGo(function () use ($channel, $swooleTimeout) {
            $swooleTimeoutScheduled = $swooleTimeout->setTimeout($this->inactivityTimeout);

            Coroutine::defer(static function () use ($channel) {
                $channel->close();
            });

            Coroutine::defer(static function () use (&$swooleTimeoutScheduled) {
                $swooleTimeoutScheduled->cancel();
            });

            foreach (($this->iterableTask)() as $observableTaskStatusUpdate) {
                if (Coroutine::isCanceled()) {
                    break;
                }

                $swooleTimeoutScheduled = $swooleTimeoutScheduled->reschedule($this->inactivityTimeout);

                $channel->push($observableTaskStatusUpdate, $this->inactivityTimeout);
            }
        });

        /**
         * @var SwooleChannelIterator<ObservableTaskStatusUpdate>
         */
        return new SwooleChannelIterator(
            channel: $channel,
            timeout: $this->inactivityTimeout,
        );
    }
}
