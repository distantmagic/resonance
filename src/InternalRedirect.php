<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class InternalRedirect implements HttpInterceptableInterface
{
    /**
     * @param array<string,string> $params
     */
    public function __construct(
        public HttpRouteSymbolInterface $routeSymbol,
        public array $params = [],
    ) {}
}
