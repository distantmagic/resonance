<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface WebSocketRPCConnectionControllerInterface
{
    public function onClose(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void;

    public function onOpen(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void;
}
