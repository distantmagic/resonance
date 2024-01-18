<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use CurlHandle;
use DateTimeImmutable;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use JsonSerializable;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Coroutine\Channel;

#[Singleton]
readonly class OllamaClient
{
    private CurlHandle $ch;

    public function __construct(
        private JsonSerializer $jsonSerializer,
        private LoggerInterface $logger,
        private OllamaLinkBuilder $ollamaLinkBuilder,
    ) {
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * @return Generator<OllamaChatToken>
     */
    public function generateChatCompletion(OllamaChatRequest $request): Generator
    {
        $channel = $this->streamJson($request, '/api/chat');

        /**
         * @var SwooleChannelIterator<object{ error: string }|object{
         *   created_at: string,
         *   message: object{
         *     content: string,
         *     role: string,
         *   },
         *   response: string,
         * }>
         */
        $swooleChannelIterator = new SwooleChannelIterator($channel);

        foreach ($swooleChannelIterator as $data) {
            if (isset($data->error)) {
                $this->logger->error($data->error);
            } else {
                yield new OllamaChatToken(
                    createdAt: new DateTimeImmutable($data->created_at),
                    message: new OllamaChatMessage(
                        content: $data->message->content,
                        role: OllamaChatRole::from($data->message->role),
                    )
                );
            }
        }
    }

    /**
     * @return Generator<OllamaCompletionToken>
     */
    public function generateCompletion(OllamaCompletionRequest $request): Generator
    {
        $channel = $this->streamJson($request, '/api/generate');

        /**
         * @var SwooleChannelIterator<object{ created_at: string, response: string }>
         */
        $swooleChannelIterator = new SwooleChannelIterator($channel);

        foreach ($swooleChannelIterator as $token) {
            yield new OllamaCompletionToken(
                createdAt: new DateTimeImmutable($token->created_at),
                response: $token->response,
            );
        }
    }

    public function generateEmbedding(OllamaEmbeddingRequest $request): OllamaEmbeddingResponse
    {
        $requestData = json_encode($request);

        curl_setopt($this->ch, CURLOPT_URL, $this->ollamaLinkBuilder->build('/api/embeddings'));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        /**
         * @var false|string $responseContent
         */
        $responseContent = curl_exec($this->ch);

        if (false === $responseContent) {
            throw new CurlException($this->ch);
        }

        $this->assertStatusCode(200);

        /**
         * @var object{ embedding: array<float> } $responseData
         */
        $responseData = $this
            ->jsonSerializer
            ->unserialize($responseContent)
        ;

        return new OllamaEmbeddingResponse($responseData->embedding);
    }

    private function assertStatusCode(int $expectedStatusCode): void
    {
        /**
         * @var int $statusCode
         */
        $statusCode = curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE);

        if ($expectedStatusCode === $statusCode) {
            return;
        }

        throw new RuntimeException(sprintf(
            'curl request finished with unexpected status code: "%s"',
            $statusCode,
        ));
    }

    private function streamJson(JsonSerializable $request, string $path): Channel
    {
        $channel = new Channel(1);
        $requestData = json_encode($request);

        $cid = go(function () use ($channel, $path, $requestData) {
            try {
                curl_setopt($this->ch, CURLOPT_URL, $this->ollamaLinkBuilder->build($path));
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $requestData);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, function (CurlHandle $ch, string $data) use ($channel) {
                    $dataChunks = explode("\n", $data);

                    foreach ($dataChunks as $dataChunk) {
                        if (!empty($dataChunk)) {
                            $channel->push(
                                $this
                                    ->jsonSerializer
                                    ->unserialize($dataChunk)
                            );
                        }
                    }

                    return strlen($data);
                });

                if (!curl_exec($this->ch)) {
                    throw new CurlException($this->ch);
                }

                $this->assertStatusCode(200);
            } finally {
                curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, null);

                $channel->close();
            }
        });

        if (!is_int($cid)) {
            throw new RuntimeException('Unable to start a coroutine');
        }

        return $channel;
    }
}
