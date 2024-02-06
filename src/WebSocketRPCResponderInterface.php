<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TPayload
 */
interface WebSocketRPCResponderInterface extends ConstraintSourceInterface
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
     * @param RPCNotification<TPayload> $rpcNotification
     */
    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCNotification $rpcNotification,
    ): void;

    /**
     * @param RPCRequest<TPayload> $rpcRequest
     */
    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void;
}
