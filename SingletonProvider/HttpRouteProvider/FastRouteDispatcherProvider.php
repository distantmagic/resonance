<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider\HttpRouteProvider;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Resonance\Attribute\Singleton;
use Resonance\InternalLinkBuilder;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider\HttpRouteProvider;

use function FastRoute\simpleDispatcher;

/**
 * @template-extends HttpRouteProvider<Dispatcher>
 */
#[Singleton(
    provides: Dispatcher::class,
    requiresCollection: SingletonCollection::HttpResponder,
)]
final readonly class FastRouteDispatcherProvider extends HttpRouteProvider
{
    public function __construct(private InternalLinkBuilder $internalLinkBuilder) {}

    public function provide(SingletonContainer $singletons): Dispatcher
    {
        return simpleDispatcher(fn (RouteCollector $routes) => $this->collectRoutes($singletons, $routes));
    }

    private function collectRoutes(
        SingletonContainer $singletons,
        RouteCollector $routes,
    ): void {
        foreach ($this->responderAttributes() as $httpResponderReflection) {
            $routes->addRoute(
                $httpResponderReflection->attribute->method->value,
                $httpResponderReflection->attribute->pattern,
                $httpResponderReflection->attribute->routeSymbol,
            );
        }
    }
}
