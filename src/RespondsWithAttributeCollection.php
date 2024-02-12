<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsWith;
use Ds\Map;
use Ds\Set;

readonly class RespondsWithAttributeCollection
{
    /**
     * @var Map<class-string<HttpResponderInterface>, Set<PHPFileReflectionClassAttribute<HttpResponderInterface,RespondsWith>>> $attributes
     */
    public Map $attributes;

    public function __construct()
    {
        $this->attributes = new Map();
    }

    /**
     * @param PHPFileReflectionClassAttribute<HttpResponderInterface,RespondsWith> $attribute
     */
    public function addAttribute(PHPFileReflectionClassAttribute $attribute): void
    {
        $className = $attribute->reflectionClass->getName();

        if (!$this->attributes->hasKey($className)) {
            $this->attributes->put($className, new Set());
        }

        $this->attributes->get($className)->add($attribute);
    }
}
