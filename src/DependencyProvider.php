<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use ReflectionClass;

readonly class DependencyProvider
{
    /**
     * @param Set<FeatureInterface>             $grantsFeatures
     * @param Set<SingletonCollectionInterface> $requiredCollections
     * @param class-string                      $providedClassName
     * @param Set<FeatureInterface>             $wantsFeatures
     */
    public function __construct(
        public Set $grantsFeatures,
        public ReflectionClass $providerReflectionClass,
        public Set $requiredCollections,
        public ?SingletonCollectionInterface $collection,
        public string $providedClassName,
        public Set $wantsFeatures,
    ) {}
}
