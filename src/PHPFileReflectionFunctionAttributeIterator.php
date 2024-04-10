<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute as BaseAttribute;
use Generator;
use IteratorAggregate;
use ReflectionFunction;

/**
 * @template TAttribute of BaseAttribute
 *
 * @template-implements IteratorAggregate<PHPFileReflectionFunctionAttribute<TAttribute>>
 */
class PHPFileReflectionFunctionAttributeIterator implements IteratorAggregate
{
    /**
     * @param iterable<ReflectionFunction> $reflectionFunctionIterator
     * @param class-string<TAttribute>     $attributeFunction
     */
    public function __construct(
        private readonly iterable $reflectionFunctionIterator,
        private readonly string $attributeFunction,
    ) {}

    /**
     * @return Generator<PHPFileReflectionFunctionAttribute<TAttribute>>
     */
    public function getIterator(): Generator
    {
        foreach ($this->reflectionFunctionIterator as $reflectionFunction) {
            $reflectionFunctionAttributeManager = new ReflectionAttributeManager($reflectionFunction);

            foreach ($reflectionFunctionAttributeManager->findAttributes($this->attributeFunction) as $attribute) {
                yield new PHPFileReflectionFunctionAttribute(
                    $reflectionFunction,
                    $attribute,
                );
            }
        }
    }
}
