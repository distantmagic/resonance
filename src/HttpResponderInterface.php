<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface HttpResponderInterface extends RequestHandlerInterface
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|ResponseInterface|self;
}
