<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use ReflectionFunction;

/**
 * @template TAttribute of object
 */
readonly class PHPFileReflectionFunctionAttribute
{
    /**
     * @param TAttribute $attribute
     */
    public function __construct(
        public ReflectionFunction $reflectionFunction,
        public object $attribute,
    ) {}
}
