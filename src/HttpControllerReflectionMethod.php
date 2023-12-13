<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\CurrentRequest;
use Distantmagic\Resonance\Attribute\CurrentResponse;
use Ds\Map;
use Generator;
use LogicException;
use ReflectionAttribute;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
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

        $this->assertReturnTypes();

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $this->extractParameterData($reflectionParameter);
        }
    }

    private function assertReturnTypes(): void
    {
        foreach ($this->extractReturnTypes() as $returnType) {
            if (!($returnType instanceof ReflectionNamedType)) {
                $this->reportError('Unsupported return type');
            }

            if ($returnType->isBuiltin() && 'void' !== $returnType->getName()) {
                $this->reportError('Only supported return type');
            }

            if (
                is_a($returnType->getName(), HttpResponderInterface::class, true)
                || is_a($returnType->getName(), HttpInterceptableInterface::class, true)
            ) {
                return;
            }

            $this->reportError(sprintf(
                'Controller handle can only return null or %s or %s',
                HttpResponderInterface::class,
                HttpInterceptableInterface::class,
            ));
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

        if (!class_exists($className) && !interface_exists($className)) {
            $this->reportError('Class does not exist: '.$className, $reflectionParameter, $type);
        }

        $this->parameters->put($name, new HttpControllerParameter(
            $reflectionParameter,
            $this->getParameterAttribute($reflectionParameter, $className),
            $className,
            $name,
        ));
    }

    /**
     * @return Generator<null|ReflectionType>
     */
    private function extractReturnTypes(): Generator
    {
        $returnType = $this->reflectionMethod->getReturnType();

        if (
            $returnType instanceof ReflectionUnionType
            || $returnType instanceof ReflectionIntersectionType
        ) {
            foreach ($returnType->getTypes() as $unionType) {
                yield $unionType;
            }

            return;
        }
        yield $returnType;

    }

    /**
     * @param class-string $className
     */
    private function getParameterAttribute(
        ReflectionParameter $reflectionParameter,
        string $className,
    ): ?Attribute {
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

        return null;
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
