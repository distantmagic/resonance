<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use RuntimeException;

/**
 * @template-implements IteratorAggregate<LlamaCppCompletionToken>
 */
readonly class LlamaCppCompletionIterator implements IteratorAggregate
{
    // strlen('data: ')
    private const COMPLETION_CHUNKED_DATA_PREFIX_LENGTH = 6;

    /**
     * @param SwooleChannelIterator<string> $responseChunks
     */
    public function __construct(
        private JsonSerializer $jsonSerializer,
        private SwooleChannelIterator $responseChunks,
    ) {}

    /**
     * @return Generator<LlamaCppCompletionToken>
     */
    public function getIterator(): Generator
    {
        foreach ($this->responseChunks as $responseChunk) {
            /**
             * @var object{
             *   content: string,
             *   stop: boolean,
             * }
             */
            $unserializedToken = $this->jsonSerializer->unserialize(
                json: $responseChunk,
                offset: self::COMPLETION_CHUNKED_DATA_PREFIX_LENGTH,
            );

            yield new LlamaCppCompletionToken(
                content: $unserializedToken->content,
            );
        }
    }

    public function stop(): void
    {
        if (SWOOLE_CHANNEL_OK !== $this->responseChunks->channel->errCode) {
            return;
        }

        if (!$this->responseChunks->channel->close()) {
            throw new RuntimeException('Unable to close coroutine channel');
        }
    }
}
