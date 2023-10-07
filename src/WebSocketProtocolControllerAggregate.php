<?php

declare(strict_types=1);

namespace Resonance;

use DomainException;
use Ds\Map;
use Swoole\Http\Request;

readonly class WebSocketProtocolControllerAggregate
{
    /**
     * @var Map<WebSocketProtocol,WebSocketProtocolControllerInterface> $protocolControllers
     */
    public Map $protocolControllers;

    public function __construct()
    {
        $this->protocolControllers = new Map();
    }

    public function resolveController(Request $request): ?WebSocketResolvedProtocolController
    {
        if (!is_array($request->header)) {
            return null;
        }

        if (!isset($request->header['sec-websocket-protocol'])) {
            return null;
        }

        /**
         * @var string
         */
        $headerProtocol = $request->header['sec-websocket-protocol'];

        foreach (new WebSocketProtocolIterator($headerProtocol) as $protocol) {
            return new WebSocketResolvedProtocolController(
                $protocol,
                $this->getProtocolController($protocol),
            );
        }

        return null;
    }

    private function getProtocolController(WebSocketProtocol $protocol): WebSocketProtocolControllerInterface
    {
        if (!$this->protocolControllers->hasKey($protocol)) {
            throw new DomainException('Unsupported WebSocket protocol: '.$protocol->value);
        }

        return $this->protocolControllers->get($protocol);
    }
}
