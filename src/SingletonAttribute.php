<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TSingleton of object
 * @template TAttribute of Attribute
 */
readonly class SingletonAttribute
{
    /**
     * @param TSingleton $singleton
     * @param TAttribute $attribute
     */
    public function __construct(
        public object $singleton,
        public Attribute $attribute,
    ) {}
}
