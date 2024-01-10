<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Symfony\Component\Routing\Generator\UrlGenerator;

#[Singleton]
readonly class InternalLinkBuilder
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private UrlGenerator $urlGenerator,
    ) {}

    /**
     * @param array<string,string> $params
     */
    public function build(HttpRouteSymbolInterface $routeSymbol, array $params = []): string
    {
        return $this->urlGenerator->generate($routeSymbol->toConstant(), $params);
    }

    /**
     * @param array<string,string> $params
     */
    public function buildUrl(HttpRouteSymbolInterface $routeSymbol, array $params = []): string
    {
        return $this->applicationConfiguration->url.$this->build($routeSymbol, $params);
    }
}
