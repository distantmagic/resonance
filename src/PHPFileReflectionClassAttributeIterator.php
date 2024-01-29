<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute as BaseAttribute;
use Generator;
use IteratorAggregate;
use ReflectionClass;

/**
 * @template TClass of object
 * @template TAttribute of BaseAttribute
 *
 * @template-implements IteratorAggregate<PHPFileReflectionClassAttribute<TClass,TAttribute>>
 */
class PHPFileReflectionClassAttributeIterator implements IteratorAggregate
{
    /**
     * @param iterable<ReflectionClass<TClass>> $reflectionClassIterator
     * @param class-string<TAttribute>          $attributeClass
     */
    public function __construct(
        private readonly iterable $reflectionClassIterator,
        private readonly string $attributeClass,
    ) {}

    /**
     * @return Generator<PHPFileReflectionClassAttribute<TClass,TAttribute>>
     */
    public function getIterator(): Generator
    {
        foreach ($this->reflectionClassIterator as $reflectionClass) {
            $reflectionClassAttributeManager = new ReflectionClassAttributeManager($reflectionClass);

            foreach ($reflectionClassAttributeManager->findAttributes($this->attributeClass) as $attribute) {
                yield new PHPFileReflectionClassAttribute(
                    $reflectionClass,
                    $attribute,
                );
            }
        }
    }
}
