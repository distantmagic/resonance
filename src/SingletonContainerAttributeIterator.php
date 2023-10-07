<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use ReflectionClass;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements IteratorAggregate<SingletonAttribute<object,TAttribute>>
 */
readonly class SingletonContainerAttributeIterator implements IteratorAggregate
{
    /**
     * @param class-string<TAttribute> $attribute
     */
    public function __construct(
        private SingletonContainer $singletons,
        private string $attribute,
    ) {}

    /**
     * @return Generator<SingletonAttribute<object,TAttribute>>
     */
    public function getIterator(): Generator
    {
        $reflectionAttributes = new PHPFileReflectionClassAttributeIterator($this->reflections(), $this->attribute);

        foreach ($reflectionAttributes as $reflectionAttribute) {
            $singleton = $this->singletons->get($reflectionAttribute->reflectionClass->getName());

            yield new SingletonAttribute($singleton, $reflectionAttribute->attribute);
        }
    }

    /**
     * @return Generator<ReflectionClass>
     */
    private function reflections(): Generator
    {
        foreach ($this->singletons->values() as $singleton) {
            yield new ReflectionClass($singleton);
        }
    }
}
