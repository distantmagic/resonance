<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Resonance\Attribute\RespondsToHttp;
use Resonance\Attribute\Singleton;
use Resonance\InternalLinkBuilder;
use Resonance\PHPProjectFiles;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

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
