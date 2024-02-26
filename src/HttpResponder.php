<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class HttpResponder implements HttpResponderInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new LogicException('This method should not be called');
    }
}
