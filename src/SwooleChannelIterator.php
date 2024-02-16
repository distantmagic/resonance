<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use RuntimeException;
use Swoole\Coroutine\Channel;

/**
 * @template TData
 *
 * @template-implements IteratorAggregate<TData>
 */
readonly class SwooleChannelIterator implements IteratorAggregate
{
    public function __construct(
        public Channel $channel,
        private float $timeout = DM_POOL_CONNECTION_TIMEOUT,
    ) {}

    public function close(): void
    {
        $this->channel->close();
    }

    /**
     * @psalm-suppress TypeDoesNotContainType false positive, swoole channel
     *     status
     *
     * @return Generator<int,TData,bool>
     */
    public function getIterator(): Generator
    {
        if (SWOOLE_CHANNEL_OK !== $this->channel->errCode) {
            throw new RuntimeException('Channel is not OK.');
        }

        do {
            /**
             * @var mixed $data explicitly mixed for typechecks
             */
            $data = $this->channel->pop($this->timeout);

            if (SWOOLE_CHANNEL_TIMEOUT === $this->channel->errCode) {
                throw new RuntimeException('Channel timed out');
            }

            if (false === $data) {
                switch ($this->channel->errCode) {
                    case SWOOLE_CHANNEL_CLOSED:
                        return;
                    case SWOOLE_CHANNEL_OK:
                        throw new RuntimeException('Using "false" is ambiguous in channels');
                }
            }

            /**
             * @psalm-suppress RedundantCondition errCode might change async
             */
            if (SWOOLE_CHANNEL_OK === $this->channel->errCode) {
                yield $data;
            }
        } while (true);
    }
}
