<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketProtocolException;

use Distantmagic\Resonance\RPCMethodInterface;
use Distantmagic\Resonance\WebSocketProtocolException;

class UnexpectedNotification extends WebSocketProtocolException
{
    public function __construct(RPCMethodInterface $rpcMethod)
    {
        parent::__construct('RPC method must expect a notification: '.$rpcMethod->getValue());
    }
}
