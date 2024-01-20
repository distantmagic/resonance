<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class WebSocketRPCConnectionController implements WebSocketRPCConnectionControllerInterface
{
    public function onClose(WebSocketAuthResolution $webSocketAuthResolution, WebSocketConnection $webSocketConnection): void {}
}
