<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use Generator;
use IteratorAggregate;
use Swoole\Coroutine\Channel;

/**
 * @template-implements IteratorAggregate<ObservableTaskSlotStatusUpdate>
 */
readonly class ObservableTaskTableSlotStatusUpdateIterator implements IteratorAggregate
{
    /**
     * @var Set<SwooleChannelIterator>
     */
    private Set $swooleChannelIterators;

    public function __construct(
        private ObservableTaskTable $observableTaskTable,
        private float $timeout = -1,
    ) {
        $this->swooleChannelIterators = new Set();
    }

    public function close(): void
    {
        foreach ($this->swooleChannelIterators as $swooleChannelIterator) {
            $swooleChannelIterator->close();
        }
    }

    /**
     * @return Generator<ObservableTaskSlotStatusUpdate>
     */
    public function getIterator(): Generator
    {
        $channel = new Channel(1);

        $observer = static function (ObservableTaskSlotStatusUpdate $statusUpdate) use ($channel): true {
            $channel->push($statusUpdate);

            return true;
        };

        $this->observableTaskTable->observers->add($observer);

        try {
            $swooleChannelIterator = new SwooleChannelIterator($channel, $this->timeout);

            $this->swooleChannelIterators->add($swooleChannelIterator);

            /**
             * @var ObservableTaskSlotStatusUpdate $observableTaskSlotStatusUpdate
             */
            foreach ($swooleChannelIterator as $observableTaskSlotStatusUpdate) {
                yield $observableTaskSlotStatusUpdate;
            }

            $this->swooleChannelIterators->remove($swooleChannelIterator);
        } finally {
            $this->observableTaskTable->observers->remove($observer);
        }

        $channel->close();
    }
}
