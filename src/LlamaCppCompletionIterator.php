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
    private const COMPLETION_CHUNKED_DATA_PREFIX_LENGTH = 6;

    /**
     * @param SwooleChannelIterator<LlamaCppClientResponseChunk> $responseChunks
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
        /**
         * Very long messages might be sent in chunks. That also means a JSON
         * might be fragmented.
         */
        $previousChunk = '';

        try {
            foreach ($this->responseChunks as $responseChunk) {
                if ($responseChunk instanceof SwooleChannelIteratorError || ObservableTaskStatus::Failed === $responseChunk->data->status) {
                    yield new LlamaCppCompletionToken(
                        content: '',
                        isFailed: true,
                        isLastToken: true,
                    );

                    break;
                }

                $previousChunk .= $responseChunk->data->chunk;

                /**
                 * @var null|object{
                 *   content: string,
                 *   stop: boolean,
                 * }
                 */
                $unserializedToken = $this->jsonSerializer->unserialize(
                    json: $previousChunk,
                    offset: self::COMPLETION_CHUNKED_DATA_PREFIX_LENGTH,
                    throw: false,
                );

                if (JSON_ERROR_NONE === json_last_error()) {
                    $previousChunk = '';
                }

                if ($unserializedToken) {
                    yield new LlamaCppCompletionToken(
                        content: $unserializedToken->content,
                        isFailed: false,
                        isLastToken: $unserializedToken->stop,
                    );
                }
            }
        } finally {
            $this->stop();
        }

        if (!empty($previousChunk)) {
            throw new RuntimeException('LlamaCppResponse left unprocessed chunks');
        }
    }

    public function stop(): void
    {
        if (SWOOLE_CHANNEL_OK !== $this->responseChunks->channel->errCode) {
            return;
        }

        $this->responseChunks->channel->close();
    }
}
