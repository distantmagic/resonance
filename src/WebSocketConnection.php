<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;
use Swoole\WebSocket\Server;

class WebSocketConnection
{
    public WebSocketConnectionStatus $status = WebSocketConnectionStatus::Open;

    public function __construct(
        public readonly Server $server,
        public readonly int $fd,
    ) {}

    public function push(string|Stringable $response): void
    {
        $this->server->push($this->fd, (string) $response);
    }
}
