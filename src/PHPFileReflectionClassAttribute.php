<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use ReflectionClass;

/**
 * @template TClass of object
 * @template TAttribute of object
 */
readonly class PHPFileReflectionClassAttribute implements ReflectionAttributeInterface
{
    /**
     * @param ReflectionClass<TClass> $reflectionClass
     * @param TAttribute              $attribute
     */
    public function __construct(
        public ReflectionClass $reflectionClass,
        public object $attribute,
    ) {}

    public function getReflectionAttributeManager(): ReflectionAttributeManager
    {
        return new ReflectionAttributeManager($this->reflectionClass);
    }
}
