<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use ReflectionClass;

readonly class DependencyProvider
{
    /**
     * @param Set<SingletonCollectionInterface> $requiredCollections
     * @param class-string                      $providedClassName
     */
    public function __construct(
        public ?FeatureInterface $grantsFeature,
        public ReflectionClass $providerReflectionClass,
        public Set $requiredCollections,
        public ?SingletonCollectionInterface $collection,
        public string $providedClassName,
    ) {}
}
