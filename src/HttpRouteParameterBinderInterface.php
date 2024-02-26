<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpRouteParameterBinderInterface
{
    public function provide(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $routeParameterValue,
    ): ?object;
}
