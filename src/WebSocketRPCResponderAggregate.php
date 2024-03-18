<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\RPCMessage;
use DomainException;
use Ds\Map;

readonly class WebSocketRPCResponderAggregate
{
    /**
     * @var Map<WebSocketRPCResponderInterface,Constraint>
     */
    public Map $cachedConstraints;

    /**
     * @var Map<RPCMethodInterface,WebSocketRPCResponderInterface> $rpcResponders
     */
    public Map $rpcResponders;

    public function __construct()
    {
        $this->cachedConstraints = new Map();
        $this->rpcResponders = new Map();
    }

    public function selectResponder(RPCMessage $rpcMessage): WebSocketRPCResponderInterface
    {
        if (!$this->rpcResponders->hasKey($rpcMessage->method)) {
            throw new DomainException('There is no responder registered for RPC method: '.$rpcMessage->method->getValue());
        }

        return $this->rpcResponders->get($rpcMessage->method);
    }
}
