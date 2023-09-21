<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use LogicException;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Resonance\Attribute\RouteParameter;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class HttpControllerReflectionMethod
{
    /**
     * @var Map<string, RouteParameter>
     */
    public Map $attributes;

    /**
     * @var Map<string, class-string>
     */
    public Map $parameters;

    public function __construct(private ReflectionMethod $reflectionMethod)
    {
        $this->attributes = new Map();
        $this->parameters = new Map();

        $this->assertReturnType();

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $this->extractParameterData($reflectionParameter);
        }
    }

    private function assertReturnType(): void
    {
        $returnType = $this->reflectionMethod->getReturnType();

        if (!($returnType instanceof ReflectionNamedType)) {
            $this->reportError('Unsupported return type');
        }

        if ($returnType->isBuiltin() && 'void' !== $returnType->getName()) {
            $this->reportError('Only supported return type');
        }

        if (!is_a($returnType->getName(), HttpResponderInterface::class, true)) {
            $this->reportError('Controller handle can only return ?HttpResponderInterface');
        }
    }

    private function errorPath(
        ?ReflectionParameter $parameter = null,
        ?ReflectionNamedType $type = null,
    ): string {
        $ret = sprintf(
            '%s@%s',
            $this->reflectionMethod->getDeclaringClass()->getName(),
            $this->reflectionMethod->getName(),
        );

        if (!isset($parameter)) {
            return $ret;
        }

        $ret .= sprintf(
            '(%s$%s)',
            isset($type) ? $type->getName().' ' : '',
            $parameter->getName(),
        );

        return $ret;
    }

    private function extractParameterData(ReflectionParameter $reflectionParameter): void
    {
        $name = $reflectionParameter->getName();
        $type = $reflectionParameter->getType();

        if (!($type instanceof ReflectionNamedType)) {
            $this->reportError('Unsupported parameter type', $reflectionParameter);
        }

        if ($type->isBuiltin()) {
            $this->reportError('Cannot inject builtin type', $reflectionParameter, $type);
        }

        $className = $type->getName();

        if (!class_exists($className)) {
            $this->reportError('Class does not exist: '.$className, $reflectionParameter, $type);
        }

        $this->parameters->put($name, $className);

        $routeParameterAttributes = $reflectionParameter->getAttributes(
            RouteParameter::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );

        if (is_a($className, Request::class, true) || is_a($className, Response::class, true)) {
            return;
        }

        if (empty($routeParameterAttributes)) {
            $this->reportError('You have to provide a RouteParameter attribute', $reflectionParameter, $type);
        }

        foreach ($routeParameterAttributes as $routeParameterAttribute) {
            if ($this->attributes->hasKey($name)) {
                $this->reportError('RouteParameter attribute is not repeatable', $reflectionParameter, $type);
            }

            $this->attributes->put($name, $routeParameterAttribute->newInstance());
        }
    }

    private function reportError(
        string $message,
        ?ReflectionParameter $parameter = null,
        ?ReflectionNamedType $type = null,
    ): never {
        throw new LogicException(sprintf(
            '%s in %s',
            $message,
            $this->errorPath($parameter, $type),
        ));
    }
}
