<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use IteratorAggregate;
use LogicException;
use ReflectionFunctionAbstract;
use ReflectionNamedType;

/**
 * @template-implements IteratorAggregate<string,class-string>
 */
readonly class SingletonFunctionParametersIterator implements IteratorAggregate
{
    public function __construct(private ReflectionFunctionAbstract $reflectionFunction) {}

    /**
     * @return Generator<string,class-string>
     */
    public function getIterator(): Generator
    {
        foreach ($this->reflectionFunction->getParameters() as $constructorParameter) {
            $type = $constructorParameter->getType();

            if (!($type instanceof ReflectionNamedType)) {
                throw new LogicException('Not a named type: '.$type::class);
            }

            $typeClassName = $type->getName();

            if (!class_exists($typeClassName) && !interface_exists($typeClassName)) {
                throw new LogicException('Class does not exist: '.$typeClassName);
            }

            yield $constructorParameter->getName() => $typeClassName;
        }
    }
}
