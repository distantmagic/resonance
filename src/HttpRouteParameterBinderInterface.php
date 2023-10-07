<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface HttpRouteParameterBinderInterface
{
    public function provide(string $routeParameterValue): ?object;
}
