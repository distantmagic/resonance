<?php

declare(strict_types=1);

namespace Resonance\HttpResponder;

use Closure;
use LogicException;
use ReflectionMethod;
use Resonance\Attribute\RouteParameter;
use Resonance\ControllerDependencies;
use Resonance\Gatekeeper;
use Resonance\HttpControllerParameterResolutionResult;
use Resonance\HttpControllerParameterResolutionStatus;
use Resonance\HttpControllerReflectionMethod;
use Resonance\HttpResponder;
use Resonance\HttpResponder\Error\BadRequest;
use Resonance\HttpResponder\Error\Forbidden;
use Resonance\HttpResponder\Error\PageNotFound;
use Resonance\HttpResponderInterface;
use Resonance\HttpRouteMatchRegistry;
use Resonance\HttpRouteParameterBinderAggregate;
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

    public function __construct(ControllerDependencies $controllerDependencies)
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
            $bindingResult = $this->bindRouteParameter(
                $request,
                $response,
                $parameterClass,
                $parameterName,
            );

            switch ($bindingResult->status) {
                case HttpControllerParameterResolutionStatus::Forbidden:
                    return $this->forbidden;
                case HttpControllerParameterResolutionStatus::NotFound:
                    return $this->pageNotFound;
                case HttpControllerParameterResolutionStatus::NotProvided:
                    return $this->badRequest;
                case HttpControllerParameterResolutionStatus::Ok:
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $resolvedParameterValues[$parameterName] = $bindingResult->value;

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
    ): HttpControllerParameterResolutionResult {
        $routeParameterValue = $this->routeMatchRegistry->get($request)->routeVars->get($attribute->from, null);

        if (is_null($routeParameterValue)) {
            return new HttpControllerParameterResolutionResult(HttpControllerParameterResolutionStatus::NotProvided);
        }

        $object = $this->routeParameterBinderAggregate->provide($parameterClass, $routeParameterValue);

        if (is_null($object)) {
            return new HttpControllerParameterResolutionResult(HttpControllerParameterResolutionStatus::NotFound);
        }

        if (!$this->gatekeeper->withRequest($request)->crud($object)->can($attribute->intent)) {
            return new HttpControllerParameterResolutionResult(HttpControllerParameterResolutionStatus::Forbidden);
        }

        return new HttpControllerParameterResolutionResult(
            HttpControllerParameterResolutionStatus::Ok,
            $object,
        );
    }

    /**
     * @param class-string $parameterClass
     */
    protected function bindRouteParameter(
        Request $request,
        Response $response,
        string $parameterClass,
        string $parameterName,
    ): HttpControllerParameterResolutionResult {
        if ($request instanceof $parameterClass) {
            return new HttpControllerParameterResolutionResult(
                HttpControllerParameterResolutionStatus::Ok,
                $request,
            );
        }

        if ($response instanceof $parameterClass) {
            return new HttpControllerParameterResolutionResult(
                HttpControllerParameterResolutionStatus::Ok,
                $response,
            );
        }

        $responderAttribute = $this->handleReflection->attributes->get($parameterName, null);

        if ($responderAttribute) {
            return $this->bindProvidableRouteParameter($request, $responderAttribute, $parameterClass);
        }

        throw new LogicException('Cannot bind controller attribute: '.$parameterName);
    }
}
