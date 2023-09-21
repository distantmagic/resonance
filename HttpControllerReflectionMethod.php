<?php

declare(strict_types=1);

namespace Resonance;

use ReflectionMethod;

readonly class HttpControllerReflectionMethod
{
    public function __construct(private ReflectionMethod $reflectionMethod) {}
}
