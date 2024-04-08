<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;

interface WebSocketAwareInterface
{
    public function onHttpConnectionUpgraded(ServerRequestInterface $request, WebSocketConnection $webSocketConnection): void;
}
