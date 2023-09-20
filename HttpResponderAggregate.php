<?php

declare(strict_types=1);

namespace Resonance;

use DomainException;
use Ds\Map;
use FastRoute\Dispatcher;
use Resonance\HttpResponder\Error\MethodNotAllowed;
use Resonance\HttpResponder\Error\PageNotFound;
use Resonance\HttpResponder\Error\ServerError;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Throwable;

readonly class HttpResponderAggregate
{
    /**
     * @var Map<HttpRouteSymbolInterface,HttpResponderInterface> $httpResponders
     */
    public Map $httpResponders;

    public function __construct(
        private Dispatcher $httpRouteDispatcher,
        private HttpRouteMatchRegistry $routeMatchRegistry,
        private MethodNotAllowed $methodNotAllowed,
        private PageNotFound $pageNotFound,
        private ServerError $serverError,
        private SessionManager $sessionManager,
    ) {
        $this->httpResponders = new Map();
    }

    public function respond(Request $request, Response $response): void
    {
        try {
            $this->selectResponder($request)->respond($request, $response);
            $this->sessionManager->persistSession($request);
        } catch (Throwable $throwable) {
            $this->serverError->respondWithThrowable($request, $response, $throwable);
        }
    }

    private function matchResponder(
        int $routeStatus,
        ?HttpRouteSymbolInterface $routeSymbol,
    ): HttpResponderInterface {
        return match ($routeStatus) {
            Dispatcher::METHOD_NOT_ALLOWED => $this->methodNotAllowed,
            Dispatcher::NOT_FOUND => $this->pageNotFound,
            Dispatcher::FOUND => $this->resolveFoundResponder($routeSymbol),

            default => throw new DomainException('Unexpected route status'),
        };
    }

    private function matchRoute(Request $request): HttpRouteMatch
    {
        if (!is_array($request->server)) {
            throw new RuntimeException('Unable to determine the request server vars');
        }

        if (!isset($request->server['request_method']) || !is_string($request->server['request_method'])) {
            throw new RuntimeException('Unable to determine the request method');
        }

        if (!isset($request->server['request_uri']) || !is_string($request->server['request_uri'])) {
            throw new RuntimeException('Unable to determine the request uri');
        }

        $dispatcherStatus = $this
            ->httpRouteDispatcher
            ->dispatch(
                $request->server['request_method'],
                $request->server['request_uri'],
            )
        ;

        return match (count($dispatcherStatus)) {
            1, 2 => new HttpRouteMatch($dispatcherStatus[0]),
            3 => match (true) {
                $dispatcherStatus[1] instanceof HttpRouteSymbolInterface => new HttpRouteMatch(
                    status: $dispatcherStatus[0],
                    handler: $dispatcherStatus[1],
                    routeVars: $dispatcherStatus[2],
                ),
                default => throw new DomainException('Unsupported route responder type'),
            },
        };
    }

    private function resolveFoundResponder(?HttpRouteSymbolInterface $routeSymbol): HttpResponderInterface
    {
        if (!$routeSymbol || !$this->httpResponders->hasKey($routeSymbol)) {
            $this->unimplementedHttpRouteResponder($routeSymbol);
        }

        return $this->httpResponders->get($routeSymbol);
    }

    private function selectResponder(Request $request): HttpResponderInterface
    {
        $routeMatchingStatus = $this->matchRoute($request);
        $this->routeMatchRegistry->set($request, $routeMatchingStatus);

        return $this->matchResponder(
            $routeMatchingStatus->status,
            $routeMatchingStatus->handler,
        );
    }

    private function unimplementedHttpRouteResponder(?HttpRouteSymbolInterface $routeSymbol): never
    {
        if ($routeSymbol) {
            throw new DomainException('Unresolved route responder: '.$routeSymbol->getName());
        }

        throw new DomainException('Unresolved route responder.');
    }
}
