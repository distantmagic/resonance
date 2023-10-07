<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InternalLinkBuilder;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

/**
 * @template-extends SingletonProvider<Dispatcher>
 */
#[Singleton(
    provides: Dispatcher::class,
    requiresCollection: SingletonCollection::HttpResponder,
)]
final readonly class FastRouteDispatcherProvider extends SingletonProvider
{
    public function __construct(private InternalLinkBuilder $internalLinkBuilder) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Dispatcher
    {
        return simpleDispatcher(fn (RouteCollector $routes) => $this->collectRoutes($phpProjectFiles, $routes));
    }

    private function collectRoutes(
        PHPProjectFiles $phpProjectFiles,
        RouteCollector $routes,
    ): void {
        foreach ($phpProjectFiles->findByAttribute(RespondsToHttp::class) as $httpResponderReflection) {
            $routes->addRoute(
                $httpResponderReflection->attribute->method->value,
                $httpResponderReflection->attribute->pattern,
                $httpResponderReflection->attribute->routeSymbol,
            );
        }
    }
}
