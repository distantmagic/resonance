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
     * @param null|non-empty-string $uniqueResponderId
     * @param array<string, string> $routeVars
     */
    public function __construct(
        public HttpRouteMatchStatus $status,
        public ?string $uniqueResponderId = null,
        array $routeVars = [],
    ) {
        $this->routeVars = new Map($routeVars);
    }
}
