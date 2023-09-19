<?php

declare(strict_types=1);

namespace Resonance;

use Stringable;
use Swoole\WebSocket\Server;

readonly class WebSocketConnection
{
    public function __construct(
        public Server $server,
        public int $fd,
    ) {}

    public function push(string|Stringable $response): void
    {
        $this->server->push($this->fd, (string) $response);
    }
}
