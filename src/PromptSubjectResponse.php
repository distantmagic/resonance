<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use IteratorAggregate;
use Swoole\Coroutine\Channel;

/**
 * @template-implements IteratorAggregate<mixed>
 */
readonly class PromptSubjectResponse implements IteratorAggregate
{
    private Channel $channel;

    public function __construct(
        private float $timeout,
    ) {
        $this->channel = new Channel(1);
    }

    public function __destruct()
    {
        $this->channel->close();
    }

    public function end(mixed $payload = null): void
    {
        try {
            if (null !== $payload) {
                $this->write($payload);
            }
        } finally {
            $this->channel->close();
        }
    }

    /**
     * @return SwooleChannelIterator<mixed>
     */
    public function getIterator(): SwooleChannelIterator
    {
        /**
         * @var SwooleChannelIterator<mixed>
         */
        return new SwooleChannelIterator(
            channel: $this->channel,
            timeout: $this->timeout,
        );
    }

    public function write(mixed $payload): void
    {
        $this->channel->push($payload);
    }
}
