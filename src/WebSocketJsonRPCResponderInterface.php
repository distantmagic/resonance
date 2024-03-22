<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TPayload
 */
interface WebSocketJsonRPCResponderInterface extends ConstraintSourceInterface
{
    public function onBeforeMessage(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void;

    public function onClose(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void;

    /**
     * @param JsonRPCNotification<TPayload> $rpcNotification
     */
    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCNotification $rpcNotification,
    ): void;

    /**
     * @param JsonRPCRequest<TPayload> $rpcRequest
     */
    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
    ): void;
}
