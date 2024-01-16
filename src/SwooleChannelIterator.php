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
    public function __construct(private Channel $channel) {}

    /**
     * @return Generator<TData>
     */
    public function getIterator(): Generator
    {
        do {
            /**
             * @var mixed $data explicitly mixed for typechecks
             */
            $data = $this->channel->pop();

            if (false === $data) {
                switch ($this->channel->errCode) {
                    case SWOOLE_CHANNEL_CLOSED:
                        return;
                    case SWOOLE_CHANNEL_OK:
                        throw new RuntimeException('Using "false" is ambiguous in channels');
                    case SWOOLE_CHANNEL_TIMEOUT:
                        throw new RuntimeException('Swoole channel timed out');
                }
            } else {
                yield $data;
            }
        } while (true);
    }
}
