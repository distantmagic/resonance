<?php

declare(strict_types=1);

namespace Resonance\WebSocketProtocolException;

use App\RPCMethod;
use Resonance\WebSocketProtocolException;

class UnexpectedRequest extends WebSocketProtocolException
{
    public function __construct(RPCMethod $rpcMethod)
    {
        parent::__construct('RPC method must not expect a response: '.$rpcMethod->value);
    }
}
