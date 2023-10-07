<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InternalLinkBuilder;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TemplatedLink;

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
