<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Symfony\Component\Routing\Generator\UrlGenerator;

#[Singleton]
readonly class InternalLinkBuilder
{
    public function __construct(private UrlGenerator $urlGenerator) {}

    /**
     * @param array<string,string> $params
     */
    public function build(HttpRouteSymbolInterface $routeSymbol, array $params = []): string
    {
        return $this->urlGenerator->generate($routeSymbol->toConstant(), $params);
    }
}
