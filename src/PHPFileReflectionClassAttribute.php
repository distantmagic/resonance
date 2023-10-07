<?php

declare(strict_types=1);

namespace Resonance;

use ReflectionClass;
use Resonance\Attribute as BaseAttribute;

/**
 * @template TClass of object
 * @template TAttribute of BaseAttribute
 */
readonly class PHPFileReflectionClassAttribute
{
    /**
     * @param ReflectionClass<TClass> $reflectionClass
     * @param TAttribute              $attribute
     */
    public function __construct(
        public ReflectionClass $reflectionClass,
        public BaseAttribute $attribute,
    ) {}
}
