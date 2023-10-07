<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use FastRoute\Dispatcher;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\EventDispatcherInterface;
use Distantmagic\Resonance\HttpRecursiveResponder;
use Distantmagic\Resonance\HttpResponder\Error\MethodNotAllowed;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponder\Error\ServerError;
use Distantmagic\Resonance\HttpResponderAggregate;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\HttpRouteMatchRegistry;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SessionManager;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

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

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpResponderAggregate
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
