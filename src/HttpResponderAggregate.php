<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Event\UnhandledException;
use Distantmagic\Resonance\HttpResponder\Error\MethodNotAllowed;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponder\Error\ServerError;
use DomainException;
use Ds\Map;
use LogicException;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Throwable;

readonly class HttpResponderAggregate
{
    /**
     * @var Map<HttpRouteSymbolInterface,HttpResponderInterface> $httpResponders
     */
    public Map $httpResponders;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private HttpRecursiveResponder $recursiveResponder,
        private HttpRouteMatchRegistry $routeMatchRegistry,
        private MethodNotAllowed $methodNotAllowed,
        private PageNotFound $pageNotFound,
        private ServerError $serverError,
        private SessionManager $sessionManager,
        private UrlMatcher $urlMatcher,
    ) {
        $this->httpResponders = new Map();
    }

    public function respond(Request $request, Response $response): void
    {
        $responder = $this->selectResponder($request);

        try {
            $this->recursiveResponder->respondRecursive($request, $response, $responder);
        } catch (Throwable $throwable) {
            $this->eventDispatcher->dispatch(new UnhandledException($throwable));
            $this->recursiveResponder->respondRecursive(
                $request,
                $response,
                $this->serverError->respondWithThrowable($request, $response, $throwable),
            );
        } finally {
            $this->sessionManager->persistSession($request);
        }
    }

    private function matchResponder(
        HttpRouteMatchStatus $routeStatus,
        ?HttpRouteSymbolInterface $routeSymbol,
    ): HttpResponderInterface {
        return match ($routeStatus) {
            HttpRouteMatchStatus::MethodNotAllowed => $this->methodNotAllowed,
            HttpRouteMatchStatus::NotFound => $this->pageNotFound,
            HttpRouteMatchStatus::Found => $this->resolveFoundResponder($routeSymbol),

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

        $this
            ->urlMatcher
            ->getContext()
            ->setMethod($request->server['request_method'])
            ->setPathInfo((string) $request->server['path_info'])
            ->setHost((string) $request->server['remote_addr'])
            ->setHttpsPort((int) $request->server['server_port'])
            ->setScheme('https')
        ;

        try {
            /**
             * @var array<string,string>
             */
            $routeMatch = $this->urlMatcher->match((string) $request->server['path_info']);
            $routeSymbol = constant($routeMatch['_route']);

            if (!($routeSymbol instanceof HttpRouteSymbolInterface)) {
                throw new LogicException('Route symbol is not an instance of HttpRouteSymbolInterface');
            }

            unset($routeMatch['_route']);

            return new HttpRouteMatch(
                status: HttpRouteMatchStatus::Found,
                handler: $routeSymbol,
                routeVars: $routeMatch,
            );
        } catch (MethodNotAllowedException $methodNotAllowed) {
            return new HttpRouteMatch(HttpRouteMatchStatus::MethodNotAllowed);
        } catch (ResourceNotFoundException $resourceNotFound) {
            return new HttpRouteMatch(HttpRouteMatchStatus::NotFound);
        }
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
