<?php

declare(strict_types=1);

namespace Resonance;

use DomainException;
use Ds\Map;
use Resonance\InputValidatedData\RPCMessage;

readonly class WebSocketRPCResponderAggregate
{
    /**
     * @var Map<RPCMethodInterface,WebSocketRPCResponderInterface> $rpcResponders
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
            throw new DomainException('Unsupported RPC method: '.$rpcMessage->method->getName());
        }

        return $this->rpcResponders->get($rpcMessage->method);
    }
}
