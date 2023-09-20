<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use FastRoute\Dispatcher;
use Resonance\Attribute\RespondsToHttp;
use Resonance\Attribute\Singleton;
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
use Symfony\Component\Console\Output\ConsoleOutputInterface;

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
        private HttpRouteMatchRegistry $routeMatchRegistry,
        private MethodNotAllowed $methodNotAllowed,
        private PageNotFound $pageNotFound,
        private ServerError $serverError,
        private SessionManager $sessionManager,
    ) {}

    public function provide(SingletonContainer $singletons, ?ConsoleOutputInterface $output = null): HttpResponderAggregate
    {
        $httpResponderAggregate = new HttpResponderAggregate(
            $this->httpRouteDispatcher,
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
