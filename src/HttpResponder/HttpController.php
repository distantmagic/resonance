<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Closure;
use Distantmagic\Resonance\Attribute\RouteParameter;
use Distantmagic\Resonance\CrudActionSubjectInterface;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerReflectionMethod;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\HttpRouteMatchRegistry;
use Distantmagic\Resonance\HttpRouteParameterBinderAggregate;
use LogicException;
use ReflectionMethod;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class HttpController extends HttpResponder
{
    protected BadRequest $badRequest;
    protected Forbidden $forbidden;
    protected Gatekeeper $gatekeeper;
    protected PageNotFound $pageNotFound;
    protected HttpRouteMatchRegistry $routeMatchRegistry;
    protected HttpRouteParameterBinderAggregate $routeParameterBinderAggregate;
    private HttpControllerReflectionMethod $handleReflection;

    /**
     * @var Closure(mixed): ?HttpResponderInterface
     */
    private Closure $handleReflectionCallback;

    public function __construct(HttpControllerDependencies $controllerDependencies)
    {
        $this->badRequest = $controllerDependencies->badRequest;
        $this->forbidden = $controllerDependencies->forbidden;
        $this->gatekeeper = $controllerDependencies->gatekeeper;
        $this->routeMatchRegistry = $controllerDependencies->routeMatchRegistry;
        $this->routeParameterBinderAggregate = $controllerDependencies->routeParameterBinderAggregate;
        $this->pageNotFound = $controllerDependencies->pageNotFound;

        $reflectionMethod = new ReflectionMethod($this, 'handle');

        $this->handleReflection = new HttpControllerReflectionMethod($reflectionMethod);

        /**
         * @var Closure(mixed): ?HttpResponderInterface
         */
        $this->handleReflectionCallback = $reflectionMethod->getClosure($this);
    }

    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        /**
         * @var array <string,mixed>
         */
        $resolvedParameterValues = [];

        foreach ($this->handleReflection->parameters as $parameterName => $parameterClass) {
            /**
             * @var mixed explicitly mixed for typechecks
             */
            $bindingResult = $this->bindRouteParameter(
                $request,
                $response,
                $parameterClass,
                $parameterName,
            );

            switch ($bindingResult) {
                case HttpControllerParameterResolutionStatus::Forbidden:
                    return $this->forbidden;
                case HttpControllerParameterResolutionStatus::NotFound:
                    return $this->pageNotFound;
                case HttpControllerParameterResolutionStatus::NotProvided:
                    return $this->badRequest;
                default:
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $resolvedParameterValues[$parameterName] = $bindingResult;

                    break;
            }
        }

        return ($this->handleReflectionCallback)(...$resolvedParameterValues);
    }

    /**
     * @param class-string $parameterClass
     */
    protected function bindProvidableRouteParameter(
        Request $request,
        RouteParameter $attribute,
        string $parameterClass,
    ): mixed {
        $routeParameterValue = $this->routeMatchRegistry->get($request)->routeVars->get($attribute->from, null);

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

        if (!$this->gatekeeper->withRequest($request)->canCrud($object, $attribute->intent)) {
            return HttpControllerParameterResolutionStatus::Forbidden;
        }

        return $object;
    }

    /**
     * @param class-string $parameterClass
     */
    protected function bindRouteParameter(
        Request $request,
        Response $response,
        string $parameterClass,
        string $parameterName,
    ): mixed {
        if ($request instanceof $parameterClass) {
            return $request;
        }

        if ($response instanceof $parameterClass) {
            return $response;
        }

        $responderAttribute = $this->handleReflection->attributes->get($parameterName, null);

        if ($responderAttribute) {
            return $this->bindProvidableRouteParameter($request, $responderAttribute, $parameterClass);
        }

        throw new LogicException('Cannot bind controller attribute: '.$parameterName);
    }
}
