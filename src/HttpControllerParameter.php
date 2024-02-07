<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\OnParameterResolution;
use Ds\Set;
use ReflectionParameter;

/**
 * @template TAttribute of Attribute
 */
readonly class HttpControllerParameter
{
    public ?OnParameterResolution $onParameterResolution;

    /**
     * @param Set<TAttribute>  $attributes
     * @param class-string     $className
     * @param non-empty-string $name
     */
    public function __construct(
        public ReflectionParameter $reflectionParameter,
        public Set $attributes,
        public string $className,
        public string $name,
    ) {
        $this->onParameterResolution = $this->findOnParameterResolutionAttribute();
    }

    private function findOnParameterResolutionAttribute(): ?OnParameterResolution
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute instanceof OnParameterResolution) {
                return $attribute;
            }
        }

        return null;
    }
}
