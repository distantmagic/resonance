<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

#[Singleton(grantsFeature: Feature::WebSocket)]
final readonly class WebSocketServerController
{
    /**
     * It is necessary to use this specific GUID.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc6455
     */
    private const HANDSHAKE_MAGIC_GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    private const MESSAGE_INVALID_HANDSHAKE = 'WebSocket invalid handshake';
    private const MESSAGE_INVALID_SEC_WEBSOCKET = 'Invalid sec-websocket-key';
    private const MESSAGE_NO_REQUEST_HEADERS = 'No request headers';
    private const MESSAGE_NO_SEC_WEBSOCKET = 'Missing sec-websocket-key';
    private const MESSAGE_NO_WEBSOCKET_CONTROLLER = 'WebSocket controller is not set for connection';
    private const MESSAGE_NOT_AUTHORIZED = 'Not authorized to open WebSocket connection';
    private const MESSAGE_PROTOCOL_NOT_SUPPORTED = 'None of the requested protocols is supported';
    private const MESSAGE_UNEXPECTED_ONOPEN = 'Websocket open event fired despite implementing custom handshake';
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
            $this->onInvalidHandshake($response, self::MESSAGE_NO_REQUEST_HEADERS);

            return;
        }

        if (!isset($request->header['sec-websocket-key'])) {
            $this->onInvalidHandshake($response, self::MESSAGE_NO_SEC_WEBSOCKET);

            return;
        }

        /**
         * @var string
         */
        $secWebSocketKey = $request->header['sec-websocket-key'];

        if (!$this->isSecWebSocketKeyValid($secWebSocketKey)) {
            $this->onInvalidHandshake($response, self::MESSAGE_INVALID_SEC_WEBSOCKET);

            return;
        }

        $controllerResolution = $this->protocolControllerAggregate->resolveController($request);

        if (!$controllerResolution) {
            $this->onInvalidHandshake($response, self::MESSAGE_PROTOCOL_NOT_SUPPORTED);

            return;
        }

        $authResolution = $controllerResolution->controller->isAuthorizedToConnect($request);

        if (!$authResolution->isAuthorizedToConnect) {
            $this->logger->debug(self::MESSAGE_NOT_AUTHORIZED);
            $response->status(403, self::MESSAGE_NOT_AUTHORIZED);
            $response->end();

            return;
        }

        $fd = $request->fd;
        $this->protocolControllerAssignments->put($fd, $controllerResolution->controller);

        $secWebSocketAccept = base64_encode(sha1($secWebSocketKey.self::HANDSHAKE_MAGIC_GUID, true));

        $response->header('connection', 'Upgrade');
        $response->header('upgrade', 'websocket');
        $response->header('sec-websocket-accept', $secWebSocketAccept);
        $response->header('sec-websocket-version', '13');
        $response->header('sec-websocket-protocol', $controllerResolution->protocol->value);

        $response->status(101);
        $response->end();

        $controllerResolution->controller->onOpen($server, $fd, $authResolution);
    }

    public function onMessage(Server $server, Frame $frame): void
    {
        if (!$this->protocolControllerAssignments->hasKey($frame->fd)) {
            $this->logger->error(self::MESSAGE_NO_WEBSOCKET_CONTROLLER);
            $server->disconnect($frame->fd, SWOOLE_WEBSOCKET_CLOSE_SERVER_ERROR);

            return;
        }

        $this->protocolControllerAssignments->get($frame->fd)->onMessage($server, $frame);
    }

    public function onOpen(Server $server, Request $request): void
    {
        $this->logger->error(self::MESSAGE_UNEXPECTED_ONOPEN);
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
        $this->logger->debug(sprintf('%s: %s', self::MESSAGE_INVALID_HANDSHAKE, $reason));

        $response->status(400, $reason);
        $response->end();
    }
}
