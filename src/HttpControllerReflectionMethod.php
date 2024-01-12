<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\CurrentRequest;
use Distantmagic\Resonance\Attribute\CurrentResponse;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Ds\Map;
use Generator;
use ReflectionAttribute;
use ReflectionClass;
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

    /**
     * @param ReflectionClass<HttpController> $reflectionClass
     */
    public function __construct(
        public ReflectionClass $reflectionClass,
        private ReflectionMethod $reflectionMethod,
    ) {
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
                throw new HttpControllerMetadataException(
                    'Unsupported return type',
                    $this->reflectionMethod,
                );
            }

            if ($returnType->isBuiltin() && 'void' !== $returnType->getName()) {
                throw new HttpControllerMetadataException(
                    'Only supported return type',
                    $this->reflectionMethod,
                );
            }

            $returnTypeClass = $returnType->getName();

            if (
                is_a($returnTypeClass, HttpResponderInterface::class, true)
                || is_a($returnTypeClass, HttpInterceptableInterface::class, true)
            ) {
                return;
            }

            throw new HttpControllerMetadataException(
                sprintf(
                    'Controller handle can only return null or %s or %s',
                    HttpResponderInterface::class,
                    HttpInterceptableInterface::class,
                ),
                $this->reflectionMethod,
            );
        }
    }

    private function extractParameterData(ReflectionParameter $reflectionParameter): void
    {
        $name = $reflectionParameter->getName();
        $type = $reflectionParameter->getType();

        if (!($type instanceof ReflectionNamedType)) {
            throw new HttpControllerMetadataException(
                'Unsupported parameter type',
                $this->reflectionMethod,
                $reflectionParameter,
            );
        }

        if ($type->isBuiltin()) {
            throw new HttpControllerMetadataException(
                'Cannot inject builtin type',
                $this->reflectionMethod,
                $reflectionParameter,
                $type,
            );
        }

        $className = $type->getName();

        if (!class_exists($className) && !interface_exists($className)) {
            throw new HttpControllerMetadataException(
                'Class does not exist: '.$className,
                $this->reflectionMethod,
                $reflectionParameter,
                $type,
            );
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
}
