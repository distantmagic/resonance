<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use CurlHandle;
use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

#[RequiresPhpExtension('curl')]
#[Singleton(provides: LlamaCppClientInterface::class)]
readonly class LlamaCppClient implements LlamaCppClientInterface
{
    public function __construct(
        private JsonSerializer $jsonSerializer,
        private LlmChatHistoryRenderer $llmChatHistoryRenderer,
        private LlamaCppConfiguration $llamaCppConfiguration,
        private LlamaCppLinkBuilder $llamaCppLinkBuilder,
        private LoggerInterface $logger,
    ) {}

    public function generateCompletion(LlamaCppCompletionRequest $request): LlamaCppCompletionIterator
    {
        $serializedRequest = $this->jsonSerializer->serialize($request->toJsonSerializable($this->llmChatHistoryRenderer));
        $responseChunks = $this->streamResponse($serializedRequest, '/completion');

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
         * @var object{ embedding: list<float> } $responseData
         */
        $responseData = $this
            ->jsonSerializer
            ->unserialize($responseContent)
        ;

        return new LlamaCppEmbedding(
            embedding: $responseData->embedding,
        );
    }

    /**
     * @return Generator<LlamaCppInfill>
     */
    public function generateInfill(LlamaCppInfillRequest $request): Generator
    {
        $serializedRequest = $this->jsonSerializer->serialize($request);
        $responseChunks = $this->streamResponse($serializedRequest, '/infill');

        foreach ($responseChunks as $responseChunk) {
            if ($responseChunk instanceof SwooleChannelIteratorError) {
                throw new RuntimeException('Unable to generate infill');
            }

            /**
             * @var object{ content: string }
             */
            $token = $this->jsonSerializer->unserialize($responseChunk->data->chunk);

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

        if (is_string($this->llamaCppConfiguration->apiKey)) {
            $headers[] = sprintf('Authorization: Bearer %s', $this->llamaCppConfiguration->apiKey);
        }

        curl_setopt($curlHandle, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);

        return $curlHandle;
    }

    /**
     * @return SwooleChannelIterator<LlamaCppClientResponseChunk>
     */
    private function streamResponse(string $requestData, string $path): SwooleChannelIterator
    {
        $channel = new Channel(1);

        SwooleCoroutineHelper::mustGo(function () use ($channel, $path, $requestData): void {
            $curlHandle = $this->createCurlHandle();

            Coroutine::defer(static function () use ($channel) {
                $channel->close();
            });

            Coroutine::defer(static function () use ($curlHandle) {
                curl_close($curlHandle);
            });

            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 180);
            curl_setopt($curlHandle, CURLOPT_POST, true);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $requestData);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($curlHandle, CURLOPT_URL, $this->llamaCppLinkBuilder->build($path));
            curl_setopt($curlHandle, CURLOPT_WRITEFUNCTION, function (CurlHandle $curlHandle, string $data) use ($channel): int {
                if ($channel->push(new LlamaCppClientResponseChunk(
                    status: ObservableTaskStatus::Running,
                    chunk: $data
                ), $this->llamaCppConfiguration->completionTokenTimeout)) {
                    return strlen($data);
                }

                return 0;
            });
            if (false === curl_exec($curlHandle)) {
                $curlErrno = curl_errno($curlHandle);

                if (CURLE_WRITE_ERROR !== $curlErrno) {
                    $this->logger->error(new CurlErrorMessage($curlHandle));

                    $channel->push(new LlamaCppClientResponseChunk(
                        status: ObservableTaskStatus::Failed,
                        chunk: '',
                    ), $this->llamaCppConfiguration->completionTokenTimeout);
                }
            } else {
                $this->assertStatusCode($curlHandle, 200);
            }
        });

        /**
         * @var SwooleChannelIterator<LlamaCppClientResponseChunk>
         */
        return new SwooleChannelIterator(
            channel: $channel,
            timeout: $this->llamaCppConfiguration->completionTokenTimeout,
        );
    }
}
