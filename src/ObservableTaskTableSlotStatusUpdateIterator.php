<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Ds\Set;
use Generator;
use IteratorAggregate;
use Swoole\Coroutine\Channel;

/**
 * @template-implements IteratorAggregate<ObservableTaskSlotStatusUpdate>
 */
readonly class ObservableTaskTableSlotStatusUpdateIterator implements IteratorAggregate
{
    private Closure $observer;
    private Channel $channel;

    public function __construct(
        private ObservableTaskTable $observableTaskTable,
        private float $timeout = -1,
    ) {
        $this->channel = new Channel(1);
        $this->observer = function (ObservableTaskSlotStatusUpdate $statusUpdate): bool {
            return $this->channel->push($statusUpdate);
        };

        $this->observableTaskTable->observers->add($this->observer);
    }

    public function __destruct()
    {
        $this->observableTaskTable->observers->remove($this->observer);
    }

    /**
     * @return Generator<ObservableTaskSlotStatusUpdate>
     */
    public function getIterator(): Generator
    {
        $swooleChannelIterator = new SwooleChannelIterator($this->channel, $this->timeout);

        /**
         * @var ObservableTaskSlotStatusUpdate $observableTaskSlotStatusUpdate
         */
        foreach ($swooleChannelIterator as $observableTaskSlotStatusUpdate) {
            yield $observableTaskSlotStatusUpdate;
        }
    }
}
