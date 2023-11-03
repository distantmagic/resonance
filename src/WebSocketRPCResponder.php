<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\RPCMessage;
use Distantmagic\Resonance\WebSocketProtocolException\UnexpectedNotification;
use Distantmagic\Resonance\WebSocketProtocolException\UnexpectedRequest;

abstract readonly class WebSocketRPCResponder implements WebSocketRPCResponderInterface
{
    public function respond(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCMessage $rpcMessage,
    ): void {
        if (is_string($rpcMessage->requestId)) {
            $this->onRequest(
                $webSocketAuthResolution,
                $webSocketConnection,
                new RPCRequest($rpcMessage->method, $rpcMessage->payload, $rpcMessage->requestId),
            );

            return;
        }

        $this->onNotification(
            $webSocketAuthResolution,
            $webSocketConnection,
            new RPCNotification($rpcMessage->method, $rpcMessage->payload)
        );
    }

    protected function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCNotification $rpcNotification,
    ): void {
        throw new UnexpectedNotification($rpcNotification->method);
    }

    protected function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void {
        throw new UnexpectedRequest($rpcRequest->method);
    }
}
