<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class HttpRouteMatch
{
    /**
     * @var Map<string, string>
     */
    public Map $routeVars;

    /**
     * @param array<string, string> $routeVars
     */
    public function __construct(
        public HttpRouteMatchStatus $status,
        public ?HttpRouteSymbolInterface $handler = null,
        array $routeVars = [],
    ) {
        $this->routeVars = new Map($routeVars);
    }
}
