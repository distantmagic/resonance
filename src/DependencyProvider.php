<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\SideEffect;
use Ds\Set;
use ReflectionClass;

/**
 * @template TProvidedClass of object
 */
readonly class DependencyProvider
{
    /**
     * @param Set<FeatureInterface>             $grantsFeatures
     * @param Set<SingletonCollectionInterface> $requiredCollections
     * @param class-string<TProvidedClass>      $providedClassName
     * @param Set<FeatureInterface>             $wantsFeatures
     */
    public function __construct(
        public Set $grantsFeatures,
        public ReflectionClass $providerReflectionClass,
        public Set $requiredCollections,
        public ?SingletonCollectionInterface $collection,
        public string $providedClassName,
        public ?SideEffect $sideEffect,
        public Set $wantsFeatures,
    ) {}
}
