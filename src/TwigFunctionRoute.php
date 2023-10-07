<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class TwigFunctionRoute
{
    public function __construct(private InternalLinkBuilder $internalLinkBuilder) {}

    /**
     * @param array<string,string> $params
     */
    public function __invoke(
        HttpRouteSymbolInterface $routeSymbol,
        array $params = [],
    ): string {
        return $this->internalLinkBuilder->build($routeSymbol, $params);
    }
}
