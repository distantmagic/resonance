<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use ReflectionClass;

readonly class PHPProjectFiles
{
    /**
     * @var Set<ReflectionClass>
     */
    private Set $reflections;

    public function __construct()
    {
        $this->reflections = new Set();
    }

    /**
     * @template TAttribute of Attribute
     *
     * @param class-string<TAttribute> $attributeClassName
     *
     * @return iterable<PHPFileReflectionClassAttribute<object,TAttribute>>
     */
    public function findByAttribute(string $attributeClassName): iterable
    {
        return new PHPFileReflectionClassAttributeIterator($this->reflections, $attributeClassName);
    }

    public function indexDirectory(string $dirname): void
    {
        $files = new PHPFileIterator($dirname);
        $reflectionsIterator = new PHPFileReflectionClassIterator($files);

        $this->indexReflections($reflectionsIterator);
    }

    /**
     * @param iterable<ReflectionClass> $reflections
     */
    private function indexReflections(iterable $reflections): void
    {
        foreach ($reflections as $reflection) {
            $this->reflections->add($reflection);
        }
    }
}
