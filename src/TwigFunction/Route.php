<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\HttpRouteSymbolInterface;
use Distantmagic\Resonance\InternalLinkBuilder;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFunction;
use RuntimeException;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class Route extends TwigFunction
{
    public function __construct(private InternalLinkBuilder $internalLinkBuilder) {}

    /**
     * @param array<string,string> $params
     */
    public function __invoke(
        HttpRouteSymbolInterface|string $routeSymbol,
        array $params = [],
    ): string {
        if (!is_string($routeSymbol)) {
            return $this->internalLinkBuilder->build($routeSymbol, $params);
        }

        $resolvedSymbol = constant(sprintf('App\\HttpRouteSymbol::%s', $routeSymbol));

        if (!($resolvedSymbol instanceof HttpRouteSymbolInterface)) {
            throw new RuntimeException(sprintf('Expected "%s"', HttpRouteSymbolInterface::class));
        }

        return $this->internalLinkBuilder->build($resolvedSymbol, $params);
    }

    public function getName(): string
    {
        return 'route';
    }
}
