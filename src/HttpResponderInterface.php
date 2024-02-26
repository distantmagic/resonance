<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Response;

interface HttpResponderInterface extends RequestHandlerInterface
{
    public function respond(ServerRequestInterface $request, Response $response): null|HttpInterceptableInterface|self;
}
