<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
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

    public function end(mixed $payload = ''): void
    {
        try {
            $this->channel->push(new PromptSubjectResponseChunk(
                isFailed: false,
                isLastChunk: true,
                isTimeout: false,
                payload: $payload,
            ));
        } finally {
            $this->channel->close();
        }
    }

    /**
     * @return Generator<PromptSubjectResponseChunk>
     */
    public function getIterator(): Generator
    {
        /**
         * @var SwooleChannelIterator<PromptSubjectResponseChunk>
         */
        $swooleChannelIterator = new SwooleChannelIterator(
            channel: $this->channel,
            timeout: $this->inactivityTimeout,
        );

        foreach ($swooleChannelIterator as $promptSubjectResponseChunk) {
            if ($promptSubjectResponseChunk instanceof SwooleChannelIteratorError) {
                yield new PromptSubjectResponseChunk(
                    isFailed: true,
                    isLastChunk: true,
                    isTimeout: $promptSubjectResponseChunk->isTimeout,
                    payload: null,
                );

                break;
            }

            yield $promptSubjectResponseChunk->data;
        }
    }

    public function write(mixed $payload): void
    {
        $this->channel->push(new PromptSubjectResponseChunk(
            isFailed: false,
            isLastChunk: false,
            isTimeout: false,
            payload: $payload,
        ));
    }
}
