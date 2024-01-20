<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\RPCMessage;

readonly class WebSocketRPCConnectionHandle
{
    public function __construct(
        public WebSocketRPCResponderAggregate $webSocketRPCResponderAggregate,
        public WebSocketAuthResolution $webSocketAuthResolution,
        public WebSocketConnection $webSocketConnection,
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
