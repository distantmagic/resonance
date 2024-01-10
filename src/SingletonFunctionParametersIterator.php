<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use LogicException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @template-implements IteratorAggregate<string,SingletonFunctionParameter>
 */
readonly class SingletonFunctionParametersIterator implements IteratorAggregate
{
    public function __construct(private ReflectionFunctionAbstract $reflectionFunction) {}

    /**
     * @return Generator<string,SingletonFunctionParameter>
     */
    public function getIterator(): Generator
    {
        foreach ($this->reflectionFunction->getParameters() as $reflectionParameter) {
            $type = $reflectionParameter->getType();

            if (!$type) {
                throw new LogicException(sprintf(
                    'Constructor parameter has no type: %s',
                    $this->getDebugParameterName($reflectionParameter),
                ));
            }

            if (!($type instanceof ReflectionNamedType)) {
                throw new LogicException(sprintf(
                    'Not a named type: "%s" in "%s"',
                    $type::class,
                    $this->getDebugParameterName($reflectionParameter),
                ));
            }

            if ($type->isBuiltin()) {
                throw new LogicException(sprintf(
                    'Parameter uses builtin type: "%s" in "%s" at "%s"',
                    $type->getName(),
                    $this->getDebugParameterName($reflectionParameter),
                    $this->reflectionFunction->getFileName(),
                ));
            }

            $typeClassName = $type->getName();

            if (!class_exists($typeClassName) && !interface_exists($typeClassName)) {
                throw new LogicException(sprintf(
                    'Class does not exist: "%s" in "%s"',
                    $typeClassName,
                    $this->getDebugParameterName($reflectionParameter),
                ));
            }

            yield $reflectionParameter->getName() => new SingletonFunctionParameter(
                className: $typeClassName,
                reflectionParameter: $reflectionParameter,
            );
        }
    }

    private function getDebugParameterName(ReflectionParameter $reflectionParameter): string
    {
        if ($this->reflectionFunction instanceof ReflectionMethod) {
            return sprintf(
                '%s($%s)',
                $this->reflectionFunction->getDeclaringClass()->getName(),
                $reflectionParameter->getName(),
            );
        }

        return sprintf(
            '$%s',
            $reflectionParameter->getName(),
        );
    }
}
