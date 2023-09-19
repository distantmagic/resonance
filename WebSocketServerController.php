<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Psr\Log\LoggerInterface;
use Resonance\Attribute\Singleton;
use Swoole\Event;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

#[Singleton]
final readonly class WebSocketServerController
{
    private const HANDSHAKE_MAGIC_GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    private const SEC_WEBSOCKET_KEY_BASE64_DECODED_BYTES_STRLEN = 16;
    private const SEC_WEBSOCKET_KEY_BASE64_STRLEN = 24;

    /**
     * @var Map<int, WebSocketProtocolControllerInterface>
     */
    private Map $protocolControllerAssignments;

    public function __construct(
        private LoggerInterface $logger,
        private WebSocketProtocolControllerAggregate $protocolControllerAggregate,
    ) {
        $this->protocolControllerAssignments = new Map();
    }

    public function onClose(Server $server, int $fd): void
    {
        if (!$this->protocolControllerAssignments->hasKey($fd)) {
            return;
        }

        $this->protocolControllerAssignments->get($fd)->onClose($server, $fd);
        $this->protocolControllerAssignments->remove($fd);
    }

    public function onHandshake(Server $server, Request $request, Response $response): void
    {
        if (!is_array($request->header)) {
            $this->onInvalidHandshake($response, 'No request headers');

            return;
        }

        if (!isset($request->header['sec-websocket-key'])) {
            $this->onInvalidHandshake($response, 'Missing sec-websocket-key');

            return;
        }

        /**
         * @var string
         */
        $secWebSocketKey = $request->header['sec-websocket-key'];

        if (!$this->isSecWebSocketKeyValid($secWebSocketKey)) {
            $this->onInvalidHandshake($response, 'Invalid sec-websocket-key');

            return;
        }

        $controllerResolution = $this->protocolControllerAggregate->resolveController($request);

        if (!$controllerResolution) {
            $this->onInvalidHandshake($response, 'None of the requested protocols is supported');

            return;
        }

        $authResolution = $controllerResolution->controller->isAuthorizedToConnect($request);

        if (!$authResolution->isAuthorizedToConnect) {
            $response->status(403, 'Not authorized to open connection');
            $response->end();

            return;
        }

        $fd = $request->fd;
        $this->protocolControllerAssignments->put($fd, $controllerResolution->controller);

        Event::defer(static function () use ($authResolution, $controllerResolution, $fd, $server) {
            $controllerResolution->controller->onOpen($server, $fd, $authResolution);
        });

        $secWebSocketAccept = base64_encode(sha1($secWebSocketKey.self::HANDSHAKE_MAGIC_GUID, true));

        $response->header('connection', 'Upgrade');
        $response->header('upgrade', 'websocket');
        $response->header('sec-websocket-accept', $secWebSocketAccept);
        $response->header('sec-websocket-version', '13');
        $response->header('sec-websocket-protocol', $controllerResolution->protocol->value);

        $response->status(101);
        $response->end();
    }

    public function onMessage(Server $server, Frame $frame): void
    {
        if (!$this->protocolControllerAssignments->hasKey($frame->fd)) {
            $this->logger->error('websocket controller is not set for connection: '.(string) $frame->fd);
            $server->disconnect($frame->fd, SWOOLE_WEBSOCKET_CLOSE_SERVER_ERROR);

            return;
        }

        $this->protocolControllerAssignments->get($frame->fd)->onMessage($server, $frame);
    }

    public function onOpen(Server $server, Request $request): void
    {
        $this->logger->error('websocket open event fired despite implementing custom handshake');
        $server->disconnect($request->fd, SWOOLE_WEBSOCKET_CLOSE_SERVER_ERROR);
    }

    private function isSecWebSocketKeyValid(string $secWebSocketKey): bool
    {
        if (self::SEC_WEBSOCKET_KEY_BASE64_STRLEN !== strlen($secWebSocketKey)) {
            return false;
        }

        $decoded = base64_decode($secWebSocketKey, true);

        if (!is_string($decoded)) {
            return false;
        }

        return self::SEC_WEBSOCKET_KEY_BASE64_DECODED_BYTES_STRLEN === strlen($decoded);
    }

    private function onInvalidHandshake(Response $response, string $reason): void
    {
        $this->logger->debug('websocket invalid handshake: '.$reason);

        $response->status(400, $reason);
        $response->end();
    }
}
