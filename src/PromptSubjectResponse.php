<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use IteratorAggregate;
use Swoole\Coroutine\Channel;

/**
 * @template-implements IteratorAggregate<PromptSubjectResponseChunk>
 */
readonly class PromptSubjectResponse implements IteratorAggregate
{
    private Channel $channel;

    public function __construct(
        private float $inactivityTimeout,
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
            $this->channel->push(new PromptSubjectResponseChunk(
                isFailed: false,
                isLastChunk: true,
                payload: $payload,
            ));
        } finally {
            $this->channel->close();
        }
    }

    /**
     * @return SwooleChannelIterator<PromptSubjectResponseChunk>
     */
    public function getIterator(): SwooleChannelIterator
    {
        /**
         * @var SwooleChannelIterator<PromptSubjectResponseChunk>
         */
        return new SwooleChannelIterator(
            channel: $this->channel,
            timeout: $this->inactivityTimeout,
        );
    }

    public function write(mixed $payload): void
    {
        $this->channel->push(new PromptSubjectResponseChunk(
            isFailed: false,
            isLastChunk: false,
            payload: $payload,
        ));
    }
}
