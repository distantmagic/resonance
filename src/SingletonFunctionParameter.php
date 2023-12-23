<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use ReflectionParameter;

readonly class SingletonFunctionParameter
{
    /**
     * @param class-string $className
     */
    public function __construct(
        public string $className,
        public ReflectionParameter $reflectionParameter,
    ) {}
}
