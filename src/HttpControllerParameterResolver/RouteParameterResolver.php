<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\RouteParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CrudActionSubjectInterface;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\HttpRouteMatchRegistry;
use Distantmagic\Resonance\HttpRouteParameterBinderAggregate;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpControllerParameterResolver<RouteParameter>
 */
#[ResolvesHttpControllerParameter(RouteParameter::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class RouteParameterResolver extends HttpControllerParameterResolver
{
    public function __construct(
        private Gatekeeper $gatekeeper,
        private HttpRouteMatchRegistry $routeMatchRegistry,
        private HttpRouteParameterBinderAggregate $routeParameterBinderAggregate,
    ) {}

    /**
     * @param RouteParameter $responderAttribute
     * @param class-string   $parameterClass
     */
    public function resolve(
        Request $request,
        Response $response,
        Attribute $responderAttribute,
        string $parameterClass,
    ): mixed {
        $routeParameterValue = $this->routeMatchRegistry->get($request)->routeVars->get($responderAttribute->from, null);

        if (is_null($routeParameterValue)) {
            return HttpControllerParameterResolutionStatus::NotProvided;
        }

        $object = $this->routeParameterBinderAggregate->provide($parameterClass, $routeParameterValue);

        if (is_null($object)) {
            return HttpControllerParameterResolutionStatus::NotFound;
        }

        if (!($object instanceof CrudActionSubjectInterface)) {
            throw new LogicException('Bound parameter cannot be subjected to Gatekeeper check');
        }

        if (!$this->gatekeeper->withRequest($request)->canCrud($object, $responderAttribute->intent)) {
            return HttpControllerParameterResolutionStatus::Forbidden;
        }

        return $object;
    }
}
