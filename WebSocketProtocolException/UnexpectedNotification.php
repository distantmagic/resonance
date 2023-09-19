<?php

declare(strict_types=1);

namespace Resonance\WebSocketProtocolException;

use App\RPCMethod;
use Resonance\WebSocketProtocolException;

class UnexpectedNotification extends WebSocketProtocolException
{
    public function __construct(RPCMethod $rpcMethod)
    {
        parent::__construct('RPC method must expect a response: '.$rpcMethod->value);
    }
}
