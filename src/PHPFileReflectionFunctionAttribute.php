<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use ReflectionFunction;

/**
 * @template TAttribute of object
 */
readonly class PHPFileReflectionFunctionAttribute implements ReflectionAttributeInterface
{
    /**
     * @param TAttribute $attribute
     */
    public function __construct(
        public ReflectionFunction $reflectionFunction,
        public object $attribute,
    ) {}

    public function getReflectionAttributeManager(): ReflectionAttributeManager
    {
        return new ReflectionAttributeManager($this->reflectionFunction);
    }
}
