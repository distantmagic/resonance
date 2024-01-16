<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use CurlHandle;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use RuntimeException;
use Swoole\Coroutine\Channel;

#[Singleton]
readonly class OllamaClient
{
    private CurlHandle $ch;

    public function __construct(
        private JsonSerializer $jsonSerializer,
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
     * @return Generator<string>
     */
    public function generateCompletion(OllamaCompletionRequest $request): Generator
    {
        $channel = new Channel(1);
        $requestData = json_encode($request);

        $cid = go(function () use ($channel, $requestData) {
            try {
                curl_setopt($this->ch, CURLOPT_URL, $this->ollamaLinkBuilder->build('/api/generate'));
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $requestData);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, function (CurlHandle $ch, string $data) use ($channel) {
                    if (!empty($data)) {
                        $channel->push(
                            $this
                                ->jsonSerializer
                                ->unserialize($data)
                        );
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

        /**
         * @var SwooleChannelIterator<object{ response: string }>
         */
        $swooleChannelIterator = new SwooleChannelIterator($channel);

        foreach ($swooleChannelIterator as $token) {
            yield $token->response;
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
}
