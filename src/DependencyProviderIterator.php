<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Set;
use Generator;
use IteratorAggregate;
use ReflectionAttribute;

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

            yield $providedClassName => new DependencyProvider(
                collection: $reflectionAttribute->attribute->collection,
                grantsFeature: $reflectionAttribute->attribute->grantsFeature,
                providedClassName: $providedClassName,
                providerReflectionClass: $reflectionAttribute->reflectionClass,
                requiredCollections: $this->findRequiredCollections($reflectionAttribute),
                wantsFeature: $reflectionAttribute->attribute->wantsFeature,
            );
        }
    }

    /**
     * @param PHPFileReflectionClassAttribute<object,Singleton> $reflectionAttribute
     *
     * @return Set<SingletonCollectionInterface> $requiredCollections
     */
    private function findRequiredCollections(PHPFileReflectionClassAttribute $reflectionAttribute): Set
    {
        $requiredCollectionsReflections = $reflectionAttribute
            ->reflectionClass
            ->getAttributes(RequiresSingletonCollection::class, ReflectionAttribute::IS_INSTANCEOF)
        ;

        /**
         * @var Set<SingletonCollectionInterface> $requiredCollections
         */
        $requiredCollections = new Set();

        foreach ($requiredCollectionsReflections as $requiredCollectionReflection) {
            $requiredCollections->add($requiredCollectionReflection->newInstance()->collection);
        }

        return $requiredCollections;
    }
}
