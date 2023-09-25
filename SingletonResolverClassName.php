<?php

declare(strict_types=1);

namespace Resonance;

readonly class SingletonResolverClassName
{
    /**
     * @param class-string $className
     * @param class-string $resolver
     */
    public function __construct(
        public string $className,
        public string $resolver,
    ) {}
}
