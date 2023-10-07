<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use FastRoute\Dispatcher;

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
        public int $status = Dispatcher::NOT_FOUND,
        public ?HttpRouteSymbolInterface $handler = null,
        array $routeVars = [],
    ) {
        $this->routeVars = new Map($routeVars);
    }
}
