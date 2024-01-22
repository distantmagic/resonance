<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use CurlHandle;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use JsonSerializable;
use RuntimeException;
use Swoole\Coroutine\Channel;

#[Singleton]
readonly class LlamaCppClient
{
    public function __construct(
        private JsonSerializer $jsonSerializer,
        private LlamaCppConfiguration $llamaCppConfiguration,
        private LlamaCppLinkBuilder $llamaCppLinkBuilder,
    ) {}

    public function generateCompletion(LlamaCppCompletionRequest $request): LlamaCppCompletionIterator
    {
        $responseChunks = $this->streamResponse($request, '/completion');

        return new LlamaCppCompletionIterator(
            $this->jsonSerializer,
            $responseChunks,
        );
    }

    public function generateEmbedding(LlamaCppEmbeddingRequest $request): LlamaCppEmbedding
    {
        $curlHandle = $this->createCurlHandle();

        $requestData = json_encode($request);

        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_URL, $this->llamaCppLinkBuilder->build('/embedding'));

        /**
         * @var false|string $responseContent
         */
        $responseContent = curl_exec($curlHandle);

        if (false === $responseContent) {
            throw new CurlException($curlHandle);
        }

        $this->assertStatusCode($curlHandle, 200);

        /**
         * @var object{ embedding: array<float> } $responseData
         */
        $responseData = $this
            ->jsonSerializer
            ->unserialize($responseContent)
        ;

        return new LlamaCppEmbedding($responseData->embedding);
    }

    /**
     * @return Generator<LlamaCppInfill>
     */
    public function generateInfill(LlamaCppInfillRequest $request): Generator
    {
        $responseChunks = $this->streamResponse($request, '/infill');

        foreach ($responseChunks as $responseChunk) {
            /**
             * @var object{ content: string }
             */
            $token = $this->jsonSerializer->unserialize($responseChunk);

            yield new LlamaCppInfill(
                after: $request->after,
                before: $request->before,
                content: $token->content,
            );
        }
    }

    public function getHealth(): LlamaCppHealthStatus
    {
        $curlHandle = $this->createCurlHandle();

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_URL, $this->llamaCppLinkBuilder->build('/health'));

        /**
         * @var false|string $responseContent
         */
        $responseContent = curl_exec($curlHandle);

        if (false === $responseContent) {
            throw new CurlException($curlHandle);
        }

        $this->assertStatusCode($curlHandle, 200);

        /**
         * @var object{ status: string } $responseData
         */
        $responseData = $this
            ->jsonSerializer
            ->unserialize($responseContent)
        ;

        return LlamaCppHealthStatus::from($responseData->status);
    }

    private function assertStatusCode(CurlHandle $curlHandle, int $expectedStatusCode): void
    {
        /**
         * @var int $statusCode
         */
        $statusCode = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);

        if ($expectedStatusCode === $statusCode) {
            return;
        }

        throw new RuntimeException(sprintf(
            'curl request finished with unexpected status code: "%s"',
            $statusCode,
        ));
    }

    private function createCurlHandle(): CurlHandle
    {
        $curlHandle = curl_init();

        /**
         * @var array<string>
         */
        $headers = [
            'Content-Type: application/json',
        ];

        if ($this->llamaCppConfiguration->apiKey) {
            $headers[] = sprintf('Authorization: Bearer %s', $this->llamaCppConfiguration->apiKey);
        }

        curl_setopt($curlHandle, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);

        return $curlHandle;
    }

    /**
     * @return SwooleChannelIterator<string>
     */
    private function streamResponse(JsonSerializable $request, string $path): SwooleChannelIterator
    {
        $channel = new Channel(1);
        $requestData = json_encode($request);

        $cid = go(function () use ($channel, $path, $requestData) {
            $curlHandle = $this->createCurlHandle();

            try {
                curl_setopt($curlHandle, CURLOPT_POST, true);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $requestData);
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($curlHandle, CURLOPT_URL, $this->llamaCppLinkBuilder->build($path));
                curl_setopt($curlHandle, CURLOPT_WRITEFUNCTION, function (CurlHandle $curlHandle, string $data) use ($channel) {
                    if ($channel->push($data, $this->llamaCppConfiguration->completionTokenTimeout)) {
                        return strlen($data);
                    }

                    return 0;
                });
                if (!curl_exec($curlHandle)) {
                    $curlErrno = curl_errno($curlHandle);

                    if (CURLE_WRITE_ERROR !== $curlErrno) {
                        throw new CurlException($curlHandle);
                    }
                } else {
                    $this->assertStatusCode($curlHandle, 200);
                }
            } finally {
                curl_close($curlHandle);

                $channel->close();
            }
        });

        if (!is_int($cid)) {
            throw new RuntimeException('Unable to start a coroutine');
        }

        /**
         * @var SwooleChannelIterator<string>
         */
        return new SwooleChannelIterator(
            channel: $channel,
            timeout: $this->llamaCppConfiguration->completionTokenTimeout,
        );
    }
}
