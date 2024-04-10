<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\CurrentRequest;
use Distantmagic\Resonance\Attribute\CurrentResponse;
use Distantmagic\Resonance\Attribute\DoctrineEntityManager;
use Distantmagic\Resonance\Attribute\SessionAuthenticated;
use Doctrine\ORM\EntityManagerInterface;
use Ds\Set;
use Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

readonly class HttpControllerReflectionMethod
{
    /**
     * @var Set<HttpControllerParameter>
     */
    public Set $parameters;

    public function __construct(private ReflectionMethod $reflectionMethod)
    {
        $this->parameters = new Set();

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

            if ($returnType->isBuiltin()) {
                if ('void' === $returnType->getName()) {
                    return;
                }

                throw new HttpControllerMetadataException(
                    'Only supported builtin return type is "void"',
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
                    'Controller handle can only return null or "%s" or "%s"',
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

        $this->parameters->add(new HttpControllerParameter(
            $reflectionParameter,
            $this->getParameterAttributes($reflectionParameter, $className),
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
     *
     * @return Set<Attribute>
     */
    private function getParameterAttributes(
        ReflectionParameter $reflectionParameter,
        string $className,
    ): Set {
        $routeParameterAttributes = $reflectionParameter->getAttributes(
            Attribute::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );

        /**
         * @var Set<Attribute>
         */
        $attributes = new Set();

        switch (count($routeParameterAttributes)) {
            case 0:
                if (is_a($className, EntityManagerInterface::class, true)) {
                    $attributes->add(new DoctrineEntityManager());
                } elseif (is_a($className, ServerRequestInterface::class, true)) {
                    $attributes->add(new CurrentRequest());
                } elseif (is_a($className, ResponseInterface::class, true)) {
                    $attributes->add(new CurrentResponse());
                } elseif (is_a($className, UserInterface::class, true)) {
                    $attributes->add(new SessionAuthenticated());
                }

                break;
            default:
                foreach ($routeParameterAttributes as $routeParameterAttribute) {
                    $attributes->add($routeParameterAttribute->newInstance());
                }

                break;
        }

        return $attributes;
    }
}
