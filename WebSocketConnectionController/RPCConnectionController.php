<?php

declare(strict_types=1);

namespace Resonance\WebSocketConnectionController;

use App\InputValidatedData\RPCMessage;
use Resonance\WebSocketAuthResolution;
use Resonance\WebSocketConnection;
use Resonance\WebSocketConnectionController;
use Resonance\WebSocketRPCResponderAggregate;

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
