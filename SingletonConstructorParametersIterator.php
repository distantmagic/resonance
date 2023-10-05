<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use IteratorAggregate;
use ReflectionClass;

/**
 * @template-implements IteratorAggregate<string,class-string>
 */
readonly class SingletonConstructorParametersIterator implements IteratorAggregate
{
    public function __construct(private ReflectionClass $reflectionClass) {}

    /**
     * @return Generator<string,class-string>
     */
    public function getIterator(): Generator
    {
        $constructorReflection = $this->reflectionClass->getConstructor();

        if ($constructorReflection) {
            yield from new SingletonFunctionParametersIterator($constructorReflection);
        }
    }
}
