<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Ds\Set;
use Generator;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<class-string,DependencyProvider>
 */
readonly class DependencyProviderIterator implements IteratorAggregate
{
    public function __construct(private PHPProjectFiles $phpProjectFiles) {}

    /**
     * @return Generator<class-string,DependencyProvider>
     */
    public function getIterator(): Generator
    {
        foreach ($this->phpProjectFiles->findByAttribute(Singleton::class) as $reflectionAttribute) {
            $providedClassName = $reflectionAttribute->attribute->provides ?? $reflectionAttribute->reflectionClass->getName();
            $reflectionClassAttributeManager = new ReflectionClassAttributeManager($reflectionAttribute->reflectionClass);

            yield $providedClassName => new DependencyProvider(
                collection: $reflectionAttribute->attribute->collection,
                grantsFeatures: $this->pluckFeature($reflectionClassAttributeManager->findAttributes(GrantsFeature::class)),
                providedClassName: $providedClassName,
                providerReflectionClass: $reflectionAttribute->reflectionClass,
                requiredCollections: $this->findRequiredCollections($reflectionClassAttributeManager),
                wantsFeatures: $this->pluckFeature($reflectionClassAttributeManager->findAttributes(WantsFeature::class)),
            );
        }
    }

    /**
     * @return Set<SingletonCollectionInterface> $requiredCollections
     */
    private function findRequiredCollections(ReflectionClassAttributeManager $reflectionClassAttributeManager): Set
    {
        $requiredCollectionAttributes = $reflectionClassAttributeManager->findAttributes(RequiresSingletonCollection::class);

        /**
         * @var Set<SingletonCollectionInterface> $requiredCollections
         */
        $requiredCollections = new Set();

        foreach ($requiredCollectionAttributes as $requiredCollectionReflection) {
            $requiredCollections->add($requiredCollectionReflection->collection);
        }

        return $requiredCollections;
    }

    /**
     * @param Set<GrantsFeature>|Set<WantsFeature> $attributes
     *
     * @return Set<FeatureInterface>
     */
    private function pluckFeature(Set $attributes): Set
    {
        /**
         * @var Set<FeatureInterface>
         */
        $ret = new Set();

        foreach ($attributes as $attribute) {
            $ret->add($attribute->feature);
        }

        return $ret;
    }
}
