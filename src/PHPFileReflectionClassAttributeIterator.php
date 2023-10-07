<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use ReflectionAttribute;
use ReflectionClass;
use Distantmagic\Resonance\Attribute as BaseAttribute;

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
        private iterable $reflectionClassIterator,
        private string $attributeClass,
    ) {}

    /**
     * @return Generator<PHPFileReflectionClassAttribute<TClass,TAttribute>>
     */
    public function getIterator(): Generator
    {
        foreach ($this->reflectionClassIterator as $reflectionClass) {
            foreach ($this->findAttributes($reflectionClass) as $attribute) {
                yield new PHPFileReflectionClassAttribute(
                    $reflectionClass,
                    $attribute,
                );
            }
        }
    }

    /**
     * @param ReflectionClass<TClass> $reflectionClass
     *
     * @return Generator<TAttribute>
     */
    private function findAttributes(ReflectionClass $reflectionClass): Generator
    {
        $reflectionAttributes = $reflectionClass->getAttributes(
            $this->attributeClass,
            ReflectionAttribute::IS_INSTANCEOF,
        );

        foreach ($reflectionAttributes as $reflectionAttribute) {
            yield $reflectionAttribute->newInstance();
        }
    }
}
