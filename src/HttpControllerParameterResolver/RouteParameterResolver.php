<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\RouteParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CrudActionSubjectInterface;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
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

    public function resolve(
        Request $request,
        Response $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        $routeParameterValue = $this->routeMatchRegistry->get($request)->routeVars->get($attribute->from, null);

        if (is_null($routeParameterValue)) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::MissingUrlParameterValue);
        }

        $object = $this->routeParameterBinderAggregate->provide($parameter->className, $routeParameterValue);

        if (is_null($object)) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::NotFound);
        }

        if (!($object instanceof CrudActionSubjectInterface)) {
            throw new LogicException('Bound parameter cannot be subjected to Gatekeeper check');
        }

        if (!$this->gatekeeper->withRequest($request)->canCrud($object, $attribute->intent)) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::Forbidden);
        }

        return new HttpControllerParameterResolution(
            HttpControllerParameterResolutionStatus::Success,
            $object,
        );
    }
}
