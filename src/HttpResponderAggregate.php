<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\HttpResponseReady;
use Distantmagic\Resonance\Event\UnhandledException;
use Distantmagic\Resonance\HttpResponder\Error\MethodNotAllowed;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponder\Error\ServerError;
use DomainException;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Throwable;

#[Singleton]
readonly class HttpResponderAggregate implements RequestHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private HttpRecursiveResponder $recursiveResponder,
        private HttpResponderCollection $httpResponderCollection,
        private HttpRouteMatchRegistry $routeMatchRegistry,
        private MethodNotAllowed $methodNotAllowed,
        private PageNotFound $pageNotFound,
        private RequestContext $requestContext,
        private ServerError $serverError,
        private SwooleConfiguration $swooleConfiguration,
        private UrlMatcher $urlMatcher,
    ) {}

    /**
     * @see https://bref.sh/docs/use-cases/http/advanced-use-cases#with-the-event-driven-function-runtime
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->respondToPsrRequest(
            $request,
            new Psr7Response(),
        );
    }

    public function respondToPsrRequest(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $responder = $this->selectResponder($request);

        try {
            return $this->recursiveResponder->respondRecursive($request, $response, $responder);
        } catch (Throwable $throwable) {
            $this->eventDispatcher->dispatch(new UnhandledException($throwable));

            return $this->recursiveResponder->respondRecursive(
                $request,
                $response,
                $this->serverError->sendThrowable($request, $response, $throwable),
            );
        } finally {
            $this->eventDispatcher->dispatch(new HttpResponseReady($responder, $request));
        }
    }

    /**
     * @param null|non-empty-string $uniqueResponderId
     */
    private function matchResponder(
        HttpRouteMatchStatus $routeStatus,
        ?string $uniqueResponderId,
    ): HttpResponderInterface {
        return match ($routeStatus) {
            HttpRouteMatchStatus::MethodNotAllowed => $this->methodNotAllowed,
            HttpRouteMatchStatus::NotFound => $this->pageNotFound,
            HttpRouteMatchStatus::Found => $this->resolveFoundResponder($uniqueResponderId),

            default => throw new DomainException('Unexpected route status'),
        };
    }

    private function matchRoute(ServerRequestInterface $request): HttpRouteMatch
    {
        $requestUri = $request->getUri();

        $this
            ->requestContext
            ->setMethod($request->getMethod())
            ->setPathInfo($requestUri->getPath())
            ->setHost($requestUri->getHost())
            ->setHttpsPort($this->swooleConfiguration->port)
            ->setScheme($requestUri->getScheme())
        ;

        try {
            /**
             * @var array<string,string>
             */
            $routeMatch = $this->urlMatcher->match($requestUri->getPath());

            $uniqueResponderId = $routeMatch['_route'];

            if (empty($uniqueResponderId)) {
                throw new RuntimeException('Invalid empty route id');
            }

            unset($routeMatch['_route']);

            return new HttpRouteMatch(
                routeVars: $routeMatch,
                status: HttpRouteMatchStatus::Found,
                uniqueResponderId: $uniqueResponderId,
            );
        } catch (MethodNotAllowedException) {
            return new HttpRouteMatch(HttpRouteMatchStatus::MethodNotAllowed);
        } catch (ResourceNotFoundException) {
            return new HttpRouteMatch(HttpRouteMatchStatus::NotFound);
        }
    }

    /**
     * @param null|non-empty-string $uniqueResponderId
     */
    private function resolveFoundResponder(?string $uniqueResponderId): HttpResponderInterface
    {
        if (is_null($uniqueResponderId) || !$this->httpResponderCollection->httpResponders->hasKey($uniqueResponderId)) {
            throw new DomainException('Unresolable route responder.');
        }

        return $this->httpResponderCollection->httpResponders->get($uniqueResponderId)->httpResponder;
    }

    private function selectResponder(ServerRequestInterface $request): HttpResponderInterface
    {
        $routeMatchingStatus = $this->matchRoute($request);

        $this->routeMatchRegistry->set($request, $routeMatchingStatus);

        return $this->matchResponder(
            $routeMatchingStatus->status,
            $routeMatchingStatus->uniqueResponderId,
        );
    }
}
