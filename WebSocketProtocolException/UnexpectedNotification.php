<?php

declare(strict_types=1);

namespace Resonance\WebSocketProtocolException;

use Resonance\RPCMethodInterface;
use Resonance\WebSocketProtocolException;

class UnexpectedNotification extends WebSocketProtocolException
{
    public function __construct(RPCMethodInterface $rpcMethod)
    {
        parent::__construct('RPC method must expect a response: '.$rpcMethod->getName());
    }
}
