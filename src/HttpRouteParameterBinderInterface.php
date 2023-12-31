<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface HttpRouteParameterBinderInterface
{
    public function provide(
        Request $request,
        Response $response,
        string $routeParameterValue,
    ): ?object;
}
