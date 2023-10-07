<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use LogicException;

readonly class InternalLinkBuilder
{
    /**
     * @var Map<HttpRouteSymbolInterface,TemplatedLink> $httpRouteHandlerPatterns
     */
    public Map $httpRouteHandlerPatterns;

    public function __construct()
    {
        $this->httpRouteHandlerPatterns = new Map();
    }

    /**
     * @param array<string,string> $params
     */
    public function build(HttpRouteSymbolInterface $routeSymbol, array $params = []): string
    {
        return $this->pattern($routeSymbol)->buildHref($params);
    }

    public function pattern(HttpRouteSymbolInterface $routeSymbol): TemplatedLinkInterface
    {
        if (!$this->httpRouteHandlerPatterns->hasKey($routeSymbol)) {
            throw new LogicException('Unsupported route pattern handler: '.$routeSymbol->getName());
        }

        return $this->httpRouteHandlerPatterns->get($routeSymbol);
    }
}
