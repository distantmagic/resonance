<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

interface HttpResponderInterface extends RequestHandlerInterface
{
    public function respond(Request $request, Response $response): null|HttpInterceptableInterface|self;
}
