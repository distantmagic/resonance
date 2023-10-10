<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\CurrentRequest;
use Distantmagic\Resonance\Attribute\CurrentResponse;
use Ds\Map;
use LogicException;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class HttpControllerReflectionMethod
{
    /**
     * @var Map<string, HttpControllerParameter>
     */
    public Map $parameters;

    public function __construct(private ReflectionMethod $reflectionMethod)
    {
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

        /**
         * This psalm error is a false positive.
         *
         * @psalm-suppress NoValue
         */
        if (!class_exists($className) && !interface_exists($className)) {
            $this->reportError('Class does not exist: '.$className, $reflectionParameter, $type);
        }

        $this->parameters->put($name, new HttpControllerParameter(
            $reflectionParameter,
            $this->getParameterAttribute($reflectionParameter, $className, $name),
            $className,
            $name,
        ));
    }

    /**
     * @param class-string $className
     */
    private function getParameterAttribute(
        ReflectionParameter $reflectionParameter,
        string $className,
        string $name,
    ): Attribute {
        $routeParameterAttributes = $reflectionParameter->getAttributes(
            Attribute::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );

        switch (count($routeParameterAttributes)) {
            case 0:
                if (is_a($className, Request::class, true)) {
                    return new CurrentRequest();
                }
                if (is_a($className, Response::class, true)) {
                    return new CurrentResponse();
                }

                break;
            case 1:
                foreach ($routeParameterAttributes as $routeParameterAttribute) {
                    return $routeParameterAttribute->newInstance();
                }

                break;
        }

        throw new LogicException('Controller parameter must have exactly one attribute: '.$name);
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
