<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use FastRoute\Dispatcher;
use Resonance\Attribute\RespondsToHttp;
use Resonance\Attribute\Singleton;
use Resonance\CSRFManager;
use Resonance\CSRFResponderAggregate;
use Resonance\Gatekeeper;
use Resonance\HttpResponder\Error\BadRequest;
use Resonance\HttpResponder\Error\Forbidden;
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
use Resonance\SiteActionSubjectAggregate;

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
        private BadRequest $badRequest,
        private CSRFManager $csrfManager,
        private CSRFResponderAggregate $csrfResponderAggregate,
        private Dispatcher $httpRouteDispatcher,
        private Forbidden $forbidden,
        private Gatekeeper $gatekeeper,
        private HttpRouteMatchRegistry $routeMatchRegistry,
        private MethodNotAllowed $methodNotAllowed,
        private PageNotFound $pageNotFound,
        private ServerError $serverError,
        private SessionManager $sessionManager,
        private SiteActionSubjectAggregate $siteActionSubjectAggregate,
    ) {}

    public function provide(SingletonContainer $singletons): HttpResponderAggregate
    {
        $httpResponderAggregate = new HttpResponderAggregate(
            $this->badRequest,
            $this->csrfManager,
            $this->csrfResponderAggregate,
            $this->httpRouteDispatcher,
            $this->forbidden,
            $this->gatekeeper,
            $this->routeMatchRegistry,
            $this->methodNotAllowed,
            $this->pageNotFound,
            $this->serverError,
            $this->sessionManager,
            $this->siteActionSubjectAggregate,
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
