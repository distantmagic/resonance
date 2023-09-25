<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider\HttpRouteProvider;

use Resonance\Attribute\Singleton;
use Resonance\InternalLinkBuilder;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider\HttpRouteProvider;
use Resonance\TemplatedLink;

/**
 * @template-extends HttpRouteProvider<InternalLinkBuilder>
 */
#[Singleton(provides: InternalLinkBuilder::class)]
final readonly class InternalLinkBuilderProvider extends HttpRouteProvider
{
    public function provide(SingletonContainer $singletons): InternalLinkBuilder
    {
        $internalLinkBuilder = new InternalLinkBuilder();

        foreach ($this->responderAttributes() as $httpResponderReflection) {
            $internalLinkBuilder->httpRouteHandlerPatterns->put(
                $httpResponderReflection->attribute->routeSymbol,
                new TemplatedLink($httpResponderReflection->attribute->pattern)
            );
        }

        return $internalLinkBuilder;
    }
}
