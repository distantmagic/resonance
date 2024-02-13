<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Google\Protobuf\Internal\Message;
use Hyperf\Coroutine\Channel\Pool as ChannelPool;
use Hyperf\Grpc\Parser;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\BidiStreamingCall;
use Hyperf\GrpcClient\ClientStreamingCall;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Hyperf\GrpcClient\GrpcClient;
use Hyperf\GrpcClient\Request;
use Hyperf\GrpcClient\ServerStreamingCall;
use InvalidArgumentException;
use Swoole\Http2\Response;

use function Hyperf\Support\retry;

/**
 * Based on Hyperf's GRPC base client
 *
 * @see https://github.com/hyperf/grpc-client/blob/b3b1c449f1c72009c4ebd155403325e5ac60a312/src/BaseClient.php
 */
abstract class GrpcBaseClient
{
    private GrpcClient $grpcClient;
    private readonly GrpcPoolConfiguration $poolConfiguration;

    /**
     * @return non-empty-string
     */
    abstract protected function getGrpcPoolName(): string;

    public function __construct(
        private readonly ChannelPool $channelPool,
        GrpcConfiguration $grpcConfiguration,
    ) {
        $this->poolConfiguration = $grpcConfiguration
            ->poolConfiguration
            ->get($this->getGrpcPoolName())
        ;

        $this->grpcClient = $this->createGrpcClient();
    }

    public function __destruct()
    {
        $this->grpcClient->close(false);
    }

    /**
     * @psalm-suppress PossiblyUnusedParam options are here for compatiblity
     *
     * @param callable $deserialize
     */
    protected function _bidiRequest(
        string $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ): BidiStreamingCall {
        $call = new BidiStreamingCall();
        $call
            ->setClient($this->getStartedClient())
            ->setMethod($method)
            ->setDeserialize($deserialize)
            ->setMetadata($metadata)
        ;

        return $call;
    }

    /**
     * @psalm-suppress PossiblyUnusedParam options are here for compatiblity
     *
     * @param callable $deserialize
     */
    protected function _clientStreamRequest(
        string $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ): ClientStreamingCall {
        $call = new ClientStreamingCall();
        $call
            ->setClient($this->getStartedClient())
            ->setMethod($method)
            ->setDeserialize($deserialize)
            ->setMetadata($metadata)
        ;

        return $call;
    }

    /**
     * @psalm-suppress PossiblyUnusedParam options are here for compatiblity
     *
     * @param callable $deserialize
     */
    protected function _serverStreamRequest(
        string $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ): ServerStreamingCall {
        $call = new ServerStreamingCall();
        $call
            ->setClient($this->getStartedClient())
            ->setMethod($method)
            ->setDeserialize($deserialize)
            ->setMetadata($metadata)
        ;

        return $call;
    }

    /**
     * @param callable $deserialize
     *
     * @return array|\Google\Protobuf\Internal\Message[]|Response[]
     */
    protected function _simpleRequest(
        string $method,
        Message $argument,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $options['headers'] = $this->pluckHeaders($options) + $metadata;

        /**
         * @var int $streamId
         */
        $streamId = retry(
            times: $this->poolConfiguration->serverRequestRetryAttempts,
            sleep: $this->poolConfiguration->serverRequestRetryInterval,
            callback: function () use ($method, $argument, $options) {
                $streamId = $this
                    ->grpcClient
                    ->send($this->buildRequest($method, $argument, $options))
                ;

                if ($streamId <= 0) {
                    // The client should not be used after this exception
                    $this->grpcClient = $this->createGrpcClient();

                    throw new GrpcClientException('Failed to send the request to server', StatusCode::INTERNAL);
                }

                return $streamId;
            },
        );

        /**
         * @var false|Response $response
         */
        $response = $this->grpcClient->recv($streamId);

        /**
         * @psalm-suppress UndefinedDocblockClass dockblock type error
         */
        return Parser::parseResponse($response ?: null, $deserialize);
    }

    /**
     * @param array<array-key,mixed> $options
     */
    private function buildRequest(string $method, Message $argument, array $options): Request
    {
        $headers = $this->pluckHeaders($options);

        return new Request($method, $argument, $headers);
    }

    private function createGrpcClient(): GrpcClient
    {
        $grpcClient = new GrpcClient($this->channelPool);
        $grpcClient->set($this->poolConfiguration->serverHostname);

        return $grpcClient;
    }

    private function getStartedClient(): GrpcClient
    {
        if (!$this->grpcClient->isRunning() && !$this->grpcClient->start()) {
            $message = sprintf(
                'Grpc client start failed with error code %d when trying to connect to %s',
                $this->grpcClient->getErrCode(),
                $this->poolConfiguration->serverHostname,
            );

            throw new GrpcClientException($message, StatusCode::INTERNAL);
        }

        return $this->grpcClient;
    }

    /**
     * @param array<array-key,mixed> $options
     *
     * @return array<array-key,mixed>
     */
    private function pluckHeaders(array $options): array
    {
        if (!isset($options['headers'])) {
            return [];
        }

        if (!is_array($options['headers'])) {
            throw new InvalidArgumentException('options[headers] has to be an array');
        }

        return $options['headers'];
    }
}
