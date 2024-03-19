<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use Swoole\Coroutine\Channel;

/**
 * @template-implements IteratorAggregate<ObservableTaskSlotStatusUpdate>
 */
readonly class ObservableTaskTableSlotStatusUpdateIterator implements IteratorAggregate
{
    public function __construct(
        private ObservableTaskTable $observableTaskTable,
        private float $timeout = -1,
    ) {}

    /**
     * @return Generator<ObservableTaskSlotStatusUpdate>
     */
    public function getIterator(): Generator
    {
        $channel = new Channel(1);

        $this->observableTaskTable->observableChannels->add($channel);

        try {
            /**
             * @var ObservableTaskSlotStatusUpdate $observableTaskSlotStatusUpdate
             */
            foreach (new SwooleChannelIterator($channel, $this->timeout) as $observableTaskSlotStatusUpdate) {
                yield $observableTaskSlotStatusUpdate;
            }
        } finally {
            $this->observableTaskTable->observableChannels->remove($channel);
        }

        $channel->close();
    }
}
