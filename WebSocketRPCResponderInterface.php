<?php

declare(strict_types=1);

namespace Resonance;

use App\InputValidatedData\RPCMessage;

interface WebSocketRPCResponderInterface
{
    public function respond(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCMessage $rpcMessage,
    ): void;
}
