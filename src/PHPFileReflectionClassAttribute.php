<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use ReflectionClass;

/**
 * @template TClass of object
 * @template TAttribute of object
 */
readonly class PHPFileReflectionClassAttribute
{
    /**
     * @param ReflectionClass<TClass> $reflectionClass
     * @param TAttribute              $attribute
     */
    public function __construct(
        public ReflectionClass $reflectionClass,
        public object $attribute,
    ) {}
}
