<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebSocketProtocolControllerInterface
{
    public function isAuthorizedToConnect(Request $request): WebSocketAuthResolution;

    public function onClose(Server $server, int $fd): void;

    public function onMessage(Server $server, Frame $frame): void;

    public function onOpen(Server $server, int $fd, WebSocketAuthResolution $webSocketAuthResolution): void;
}
