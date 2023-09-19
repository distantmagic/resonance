<?php

declare(strict_types=1);

namespace Resonance;

use App\InputValidatedData\RPCMessage;
use App\RPCMethod;
use DomainException;
use Ds\Map;

readonly class WebSocketRPCResponderAggregate
{
    /**
     * @var Map<RPCMethod,WebSocketRPCResponderInterface> $rpcResponders
     */
    public Map $rpcResponders;

    public function __construct()
    {
        $this->rpcResponders = new Map();
    }

    public function respond(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCMessage $rpcMessage,
    ): void {
        $this
            ->selectResponder($rpcMessage)
            ->respond($webSocketAuthResolution, $webSocketConnection, $rpcMessage)
        ;
    }

    private function selectResponder(RPCMessage $rpcMessage): WebSocketRPCResponderInterface
    {
        if (!$this->rpcResponders->hasKey($rpcMessage->method)) {
            throw new DomainException('Unsupported RPC method: '.$rpcMessage->method->value);
        }

        return $this->rpcResponders->get($rpcMessage->method);
    }
}
