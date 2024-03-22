<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\JsonRPCMessage;
use DomainException;
use Ds\Map;

readonly class WebSocketJsonRPCResponderAggregate
{
    /**
     * @var Map<WebSocketJsonRPCResponderInterface,Constraint>
     */
    public Map $cachedConstraints;

    /**
     * @var Map<JsonRPCMethodInterface,WebSocketJsonRPCResponderInterface> $rpcResponders
     */
    public Map $rpcResponders;

    public function __construct()
    {
        $this->cachedConstraints = new Map();
        $this->rpcResponders = new Map();
    }

    public function selectResponder(JsonRPCMessage $rpcMessage): WebSocketJsonRPCResponderInterface
    {
        if (!$this->rpcResponders->hasKey($rpcMessage->method)) {
            throw new DomainException('There is no responder registered for RPC method: '.$rpcMessage->method->getValue());
        }

        return $this->rpcResponders->get($rpcMessage->method);
    }
}
