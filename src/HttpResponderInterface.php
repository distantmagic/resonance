<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpResponderInterface
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|ResponseInterface|self;
}
