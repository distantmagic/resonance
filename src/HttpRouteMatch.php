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
     * @param null|class-string<HttpResponderInterface> $responderClass
     * @param array<string, string>                     $routeVars
     */
    public function __construct(
        public HttpRouteMatchStatus $status,
        public ?string $responderClass = null,
        array $routeVars = [],
    ) {
        $this->routeVars = new Map($routeVars);
    }
}
