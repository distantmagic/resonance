<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketConnectionController;

use Distantmagic\Resonance\InputValidatedData\RPCMessage;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketConnectionController;
use Distantmagic\Resonance\WebSocketRPCResponderAggregate;

readonly class RPCConnectionController extends WebSocketConnectionController
{
    public function __construct(
        private WebSocketRPCResponderAggregate $webSocketRPCResponderAggregate,
        private WebSocketAuthResolution $webSocketAuthResolution,
        private WebSocketConnection $webSocketConnection,
    ) {}

    public function onRPCMessage(RPCMessage $rpcMessage): void
    {
        $this
            ->webSocketRPCResponderAggregate
            ->respond(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
                $rpcMessage
            )
        ;
    }
}
