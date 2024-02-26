<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

interface HttpRouteParameterBinderInterface
{
    public function provide(
        ServerRequestInterface $request,
        Response $response,
        string $routeParameterValue,
    ): ?object;
}
