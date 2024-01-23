<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\WebSocketProtocolException\UnexpectedNotification;
use Distantmagic\Resonance\WebSocketProtocolException\UnexpectedRequest;

/**
 * @template TPayload
 *
 * @template-implements WebSocketRPCResponderInterface<TPayload>
 */
abstract readonly class WebSocketRPCResponder implements WebSocketRPCResponderInterface
{
    public function onClose(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void {}

    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCNotification $rpcNotification,
    ): void {
        throw new UnexpectedNotification($rpcNotification->method);
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void {
        throw new UnexpectedRequest($rpcRequest->method);
    }
}
