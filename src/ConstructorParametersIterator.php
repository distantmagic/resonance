<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use IteratorAggregate;
use ReflectionClass;
use ReflectionParameter;

/**
 * @template-implements IteratorAggregate<ReflectionParameter>
 */
readonly class ConstructorParametersIterator implements IteratorAggregate
{
    public function __construct(private ReflectionClass $reflectionClass) {}

    /**
     * @return Generator<ReflectionParameter>
     */
    public function getIterator(): Generator
    {
        $constructorReflection = $this->reflectionClass->getConstructor();

        if (!$constructorReflection) {
            // class does not have a constructor
            return;
        }

        foreach ($constructorReflection->getParameters() as $parameter) {
            yield $parameter;
        }
    }
}
