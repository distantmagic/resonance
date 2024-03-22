<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class WebSocketJsonRPCConnectionController implements WebSocketJsonRPCConnectionControllerInterface
{
    public function onClose(WebSocketAuthResolution $webSocketAuthResolution, WebSocketConnection $webSocketConnection): void {}
}
