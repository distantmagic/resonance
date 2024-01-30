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
                grantsFeature: $reflectionClassAttributeManager->findAttribute(GrantsFeature::class)?->feature,
                providedClassName: $providedClassName,
                providerReflectionClass: $reflectionAttribute->reflectionClass,
                requiredCollections: $this->findRequiredCollections($reflectionClassAttributeManager),
                wantsFeature: $reflectionClassAttributeManager->findAttribute(WantsFeature::class)?->feature,
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
}
