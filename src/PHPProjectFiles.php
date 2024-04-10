<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use ReflectionClass;
use ReflectionFunction;

readonly class PHPProjectFiles
{
    /**
     * @var Set<ReflectionClass>
     */
    public Set $reflectionClasses;

    /**
     * @var Set<ReflectionFunction>
     */
    public Set $reflectionFunctions;

    public function __construct()
    {
        $this->reflectionClasses = new Set();
        $this->reflectionFunctions = new Set();
    }

    /**
     * @template TAttribute of Attribute
     *
     * @param class-string<TAttribute> $attributeClassName
     *
     * @return iterable<TAttribute>
     */
    public function findAttribute(string $attributeClassName): iterable
    {
        foreach ($this->findClassByAttribute($attributeClassName) as $reflectionClassAttribute) {
            yield $reflectionClassAttribute->attribute;
        }

        foreach ($this->findFunctionByAttribute($attributeClassName) as $reflectionFunctionAttribute) {
            yield $reflectionFunctionAttribute->attribute;
        }
    }

    /**
     * @template TAttribute of Attribute
     *
     * @param class-string<TAttribute> $attributeClassName
     *
     * @return iterable<PHPFileReflectionClassAttribute<object,TAttribute>>
     */
    public function findClassByAttribute(string $attributeClassName): iterable
    {
        return new PHPFileReflectionClassAttributeIterator($this->reflectionClasses, $attributeClassName);
    }

    /**
     * @template TAttribute of Attribute
     *
     * @param class-string<TAttribute> $attributeClassName
     *
     * @return iterable<PHPFileReflectionFunctionAttribute<TAttribute>>
     */
    public function findFunctionByAttribute(string $attributeClassName): iterable
    {
        return new PHPFileReflectionFunctionAttributeIterator($this->reflectionFunctions, $attributeClassName);
    }

    public function indexDirectory(string $dirname): void
    {
        $files = new PHPFileIterator($dirname);

        foreach (new PHPFileReflectionClassIterator($files) as $reflection) {
            $this->reflectionClasses->add($reflection);
        }

        foreach (new PHPFileReflectionFunctionIterator($files) as $reflection) {
            $this->reflectionFunctions->add($reflection);
        }
    }
}
