<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketProtocolException;

use Distantmagic\Resonance\RPCMethodInterface;
use Distantmagic\Resonance\WebSocketProtocolException;

class UnexpectedRequest extends WebSocketProtocolException
{
    public function __construct(RPCMethodInterface $rpcMethod)
    {
        parent::__construct('RPC method must not expect a response: '.$rpcMethod->getName());
    }
}
