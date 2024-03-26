<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Generator;
use IteratorAggregate;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * @psalm-import-type TIterableTaskCallback from ObservableTask
 *
 * @template-implements IteratorAggregate<ObservableTaskStatusUpdate>
 */
readonly class ObservableTaskTimeoutIterator implements IteratorAggregate
{
    /**
     * @var Closure():iterable<ObservableTaskStatusUpdate>
     */
    private Closure $iterableTask;

    /**
     * @param TIterableTaskCallback $iterableTask
     */
    public function __construct(
        callable $iterableTask,
        private float $inactivityTimeout,
    ) {
        $this->iterableTask = Closure::fromCallable($iterableTask);
    }

    /**
     * @return Generator<ObservableTaskStatusUpdate>
     */
    public function __invoke(): Generator
    {
        return $this->getIterator();
    }

    /**
     * @psalm-suppress UnusedVariable $generatorCoroutineId is used, just asynchronously
     *
     * @return Generator<ObservableTaskStatusUpdate>
     */
    public function getIterator(): Generator
    {
        /**
         * @var null|int $generatorCoroutineId
         */
        $generatorCoroutineId = null;

        $channel = new Channel(1);

        $swooleTimeout = new SwooleTimeout(static function () use ($channel, &$generatorCoroutineId) {
            $channel->push(new ObservableTaskStatusUpdate(
                ObservableTaskStatus::TimedOut,
                null,
            ));

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
        $swooleChannelIterator = new SwooleChannelIterator(
            channel: $channel,
            timeout: $this->inactivityTimeout,
        );

        foreach ($swooleChannelIterator as $observableTaskStatusUpdate) {
            if ($observableTaskStatusUpdate instanceof SwooleChannelIteratorError) {
                if ($observableTaskStatusUpdate->isTimeout) {
                    yield new ObservableTaskStatusUpdate(ObservableTaskStatus::TimedOut, null);
                } else {
                    yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Failed, null);
                }

                break;
            }

            yield $observableTaskStatusUpdate->data;
        }
    }
}
