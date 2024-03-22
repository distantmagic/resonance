<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\WebSocketProtocolException\UnexpectedNotification;
use Distantmagic\Resonance\WebSocketProtocolException\UnexpectedRequest;

/**
 * @template TPayload
 *
 * @template-implements WebSocketJsonRPCResponderInterface<TPayload>
 */
abstract readonly class WebSocketJsonRPCResponder implements WebSocketJsonRPCResponderInterface
{
    public function onBeforeMessage(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void {}

    public function onClose(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void {}

    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCNotification $rpcNotification,
    ): void {
        throw new UnexpectedNotification($rpcNotification->method);
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
    ): void {
        throw new UnexpectedRequest($rpcRequest->method);
    }
}
