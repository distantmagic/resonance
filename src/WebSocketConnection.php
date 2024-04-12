<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Stringable;
use Swoole\WebSocket\Server;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 */
class WebSocketConnection
{
    public WebSocketConnectionStatus $status = WebSocketConnectionStatus::Open;

    public function __construct(
        public readonly int $fd,
        public LoggerInterface $logger,
        public readonly ServerRequestInterface $request,
        public readonly Server $server,
    ) {}

    public function close(
        int $code = SWOOLE_WEBSOCKET_CLOSE_POLICY_ERROR,
        string $reason = 'Connection closed due to policy error',
    ): bool {
        $this->logger->debug(sprintf('websocket_close(%s)', $reason));

        return $this->server->disconnect($this->fd, $code, $reason);
    }

    public function push(string|Stringable $response): bool
    {
        return $this->server->push($this->fd, (string) $response);
    }
}
