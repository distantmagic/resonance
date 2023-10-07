<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute as BaseAttribute;
use ReflectionClass;

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
