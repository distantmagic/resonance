<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use ReflectionParameter;

/**
 * @template TAttribute of Attribute
 */
readonly class HttpControllerParameter
{
    /**
     * @param TAttribute   $attribute
     * @param class-string $className
     */
    public function __construct(
        public ReflectionParameter $reflectionParameter,
        public ?Attribute $attribute,
        public string $className,
        public string $name,
    ) {}
}
