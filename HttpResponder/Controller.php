<?php

declare(strict_types=1);

namespace Resonance\HttpResponder;

use Closure;
use DomainException;
use Ds\Map;
use LogicException;
use ReflectionMethod;
use ReflectionNamedType;
use Resonance\Attribute\RouteParameter;
use Resonance\ControllerDependencies;
use Resonance\Gatekeeper;
use Resonance\HttpResponder;
use Resonance\HttpResponder\Error\BadRequest;
use Resonance\HttpResponder\Error\Forbidden;
use Resonance\HttpResponder\Error\PageNotFound;
use Resonance\HttpResponderInterface;
use Resonance\HttpRouteMatchRegistry;
use Resonance\HttpRouteParameterBinderAggregate;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class Controller extends HttpResponder
{
    protected BadRequest $badRequest;
    protected Forbidden $forbidden;
    protected Gatekeeper $gatekeeper;
    protected PageNotFound $pageNotFound;
    protected HttpRouteMatchRegistry $routeMatchRegistry;
    protected HttpRouteParameterBinderAggregate $routeParameterBinderAggregate;

    /**
     * @var Map<string, RouteParameter>
     */
    private Map $responderAttributes;

    /**
     * @var Map<string, class-string>
     */
    private Map $responderParameters;

    /**
     * @var Closure(mixed): ?HttpResponderInterface
     */
    private Closure $respondWithParametersClosure;

    public function __construct(ControllerDependencies $controllerDependencies)
    {
        $this->badRequest = $controllerDependencies->badRequest;
        $this->forbidden = $controllerDependencies->forbidden;
        $this->gatekeeper = $controllerDependencies->gatekeeper;
        $this->routeMatchRegistry = $controllerDependencies->routeMatchRegistry;
        $this->routeParameterBinderAggregate = $controllerDependencies->routeParameterBinderAggregate;
        $this->pageNotFound = $controllerDependencies->pageNotFound;

        $this->responderAttributes = new Map();
        $this->responderParameters = new Map();

        $reflectionMethod = new ReflectionMethod($this, 'handle');

        $returnType = $reflectionMethod->getReturnType();

        if (!($returnType instanceof ReflectionNamedType)) {
            throw new LogicException('Unsupported return type: '.$returnType::class);
        }

        if ($returnType->isBuiltin() || !is_a($returnType->getName(), HttpResponderInterface::class, true)) {
            throw new LogicException('Controller handle can only return HttpResponderInterface or null');
        }

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $name = $reflectionParameter->getName();
            $type = $reflectionParameter->getType();

            if (!($type instanceof ReflectionNamedType)) {
                throw new DomainException('Unsupported parameter type: '.$type::class);
            }

            if ($type->isBuiltin()) {
                throw new LogicException('Cannot inject builtin type into: '.$name);
            }

            $className = $type->getName();

            if (!class_exists($className)) {
                throw new LogicException('Class does not exist: '.$className);
            }

            $this->responderParameters->put($name, $className);

            $routeParameterAttributes = $reflectionParameter->getAttributes(RouteParameter::class);

            if (!is_a($className, Request::class, true) && !is_a($className, Response::class, true)) {
                if (empty($routeParameterAttributes)) {
                    throw new LogicException('You have to provide a RouteParameter attribute with parameter resolution info for: '.$className.' '.$name);
                }

                foreach ($routeParameterAttributes as $routeParameterAttribute) {
                    if ($this->responderAttributes->hasKey($name)) {
                        throw new LogicException('RouteParameter attribute is not repeatable: '.$this::class);
                    }

                    $this->responderAttributes->put($name, $routeParameterAttribute->newInstance());
                }
            }
        }

        /**
         * @var Closure(mixed): ?HttpResponderInterface
         */
        $this->respondWithParametersClosure = $reflectionMethod->getClosure($this);
    }

    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        $resolvedParameterValues = [];

        foreach ($this->responderParameters as $parameterName => $parameterClass) {
            $responderAttribute = $this->responderAttributes->get($parameterName, null);

            if ($responderAttribute) {
                $routeParameterValue = $this->routeMatchRegistry->get($request)->routeVars->get($responderAttribute->from, null);

                if (is_null($routeParameterValue)) {
                    return $this->badRequest;
                }

                $parameterClass = $this->responderParameters->get($parameterName);
                $object = $this->routeParameterBinderAggregate->provide($parameterClass, $routeParameterValue);

                if (is_null($object)) {
                    return $this->pageNotFound;
                }

                if (!$this->gatekeeper->withRequest($request)->crud($object)->can($responderAttribute->intent)) {
                    return $this->forbidden;
                }

                $resolvedParameterValues[$parameterName] = $object;
            } elseif ($request instanceof $parameterClass) {
                $resolvedParameterValues[$parameterName] = $request;
            } elseif ($response instanceof $parameterClass) {
                $resolvedParameterValues[$parameterName] = $response;
            } else {
                throw new LogicException('Cannot bind controller attribute: '.$parameterName);
            }
        }

        return ($this->respondWithParametersClosure)(...$resolvedParameterValues);
    }
}
