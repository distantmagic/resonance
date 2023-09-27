<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use FastRoute\Dispatcher;
use Resonance\Attribute\RespondsToHttp;
use Resonance\Attribute\Singleton;
use Resonance\EventDispatcherInterface;
use Resonance\HttpRecursiveResponder;
use Resonance\HttpResponder\Error\MethodNotAllowed;
use Resonance\HttpResponder\Error\PageNotFound;
use Resonance\HttpResponder\Error\ServerError;
use Resonance\HttpResponderAggregate;
use Resonance\HttpResponderInterface;
use Resonance\HttpRouteMatchRegistry;
use Resonance\SessionManager;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpResponderAggregate>
 */
#[Singleton(
    provides: HttpResponderAggregate::class,
    requiresCollection: SingletonCollection::HttpResponder,
)]
final readonly class HttpResponderAggregateProvider extends SingletonProvider
{
    public function __construct(
        private Dispatcher $httpRouteDispatcher,
        private EventDispatcherInterface $eventDispatcher,
        private HttpRecursiveResponder $recursiveResponder,
        private HttpRouteMatchRegistry $routeMatchRegistry,
        private MethodNotAllowed $methodNotAllowed,
        private PageNotFound $pageNotFound,
        private ServerError $serverError,
        private SessionManager $sessionManager,
    ) {}

    public function provide(SingletonContainer $singletons): HttpResponderAggregate
    {
        $httpResponderAggregate = new HttpResponderAggregate(
            $this->httpRouteDispatcher,
            $this->eventDispatcher,
            $this->recursiveResponder,
            $this->routeMatchRegistry,
            $this->methodNotAllowed,
            $this->pageNotFound,
            $this->serverError,
            $this->sessionManager,
        );

        foreach ($this->collectResponders($singletons) as $httpResponderAttribute) {
            $httpResponderAggregate->httpResponders->put(
                $httpResponderAttribute->attribute->routeSymbol,
                $httpResponderAttribute->singleton,
            );
        }

        return $httpResponderAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpResponderInterface,RespondsToHttp>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpResponderInterface::class,
            RespondsToHttp::class,
        );
    }
}
