<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebSocketProtocolControllerInterface
{
    public function isAuthorizedToConnect(ServerRequestInterface $request): WebSocketAuthResolution;

    public function onClose(int $fd): void;

    public function onMessage(Server $server, Frame $frame): void;

    public function onOpen(Server $server, int $fd, WebSocketAuthResolution $webSocketAuthResolution): void;
}
