<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\RespondsToHttp;
use Resonance\Attribute\Singleton;
use Resonance\InternalLinkBuilder;
use Resonance\PHPProjectFiles;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Resonance\TemplatedLink;

/**
 * @template-extends SingletonProvider<InternalLinkBuilder>
 */
#[Singleton(provides: InternalLinkBuilder::class)]
final readonly class InternalLinkBuilderProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): InternalLinkBuilder
    {
        $internalLinkBuilder = new InternalLinkBuilder();

        foreach ($phpProjectFiles->findByAttribute(RespondsToHttp::class) as $httpResponderReflection) {
            $internalLinkBuilder->httpRouteHandlerPatterns->put(
                $httpResponderReflection->attribute->routeSymbol,
                new TemplatedLink($httpResponderReflection->attribute->pattern)
            );
        }

        return $internalLinkBuilder;
    }
}
