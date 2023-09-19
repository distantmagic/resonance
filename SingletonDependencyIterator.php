<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Queue;
use Ds\Set;
use Generator;
use IteratorAggregate;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use Resonance\Attribute\Singleton;

/**
 * @template-implements IteratorAggregate<SingletonDependency>
 */
readonly class SingletonDependencyIterator implements IteratorAggregate
{
    /**
     * @param PHPFileReflectionClassAttributeIterator<object,Singleton> $fileReflectionClassAttributeIterator
     */
    public function __construct(
        private SingletonContainer $singletons,
        private PHPFileReflectionClassAttributeIterator $fileReflectionClassAttributeIterator
    ) {}

    /**
     * @return Generator<SingletonDependency>
     */
    public function getIterator(): Generator
    {
        $singletonDependencyMap = $this->buildSingletonDependencyMap();

        foreach ($this->topologicalSort($singletonDependencyMap)->reversed() as $className) {
            yield new SingletonDependency(
                $className,
                $singletonDependencyMap->providers->get($className),
            );
        }
    }

    private function buildSingletonDependencyMap(): SingletonDependencyMap
    {
        /**
         * @var Map<mixed,Set<class-string>> $collections
         */
        $collections = new Map();

        /**
         * @var Map<class-string,Set<class-string>> $dependencies
         */
        $dependencies = new Map();

        /**
         * @var Map<class-string,class-string> $providers
         */
        $providers = new Map();

        /**
         * @var Map<class-string,mixed> $requiresCollection
         */
        $requiresCollection = new Map();

        foreach ($this->fileReflectionClassAttributeIterator as $classAttributeReflection) {
            $reflectionClass = $classAttributeReflection->reflectionClass;
            $reflectionClassName = $reflectionClass->getName();
            $attribute = $classAttributeReflection->attribute;

            if ($attribute->provides) {
                $className = $attribute->provides;
                $providers->put($attribute->provides, $reflectionClassName);
            } else {
                $className = $reflectionClassName;
                $providers->put($reflectionClassName, $reflectionClassName);
            }

            if (isset($attribute->collection)) {
                if (!$collections->hasKey($attribute->collection)) {
                    $collections->put($attribute->collection, new Set());
                }

                $collection = $collections->get($attribute->collection);
                $collection->add($className);
            }

            if (isset($attribute->requiresCollection)) {
                $requiresCollection->put($className, $attribute->requiresCollection);
            }

            if (!$dependencies->hasKey($className)) {
                $dependencies->put($className, new Set());
            }

            $classDependencies = $dependencies->get($className);

            foreach ($this->getClassConstructorParameters($reflectionClass) as $parameterClassName) {
                if (!$this->singletons->has($parameterClassName)) {
                    $classDependencies->add($parameterClassName);
                }
            }
        }

        /**
         * @var mixed $collectionName explicitly mixed for typechecks
         */
        foreach ($requiresCollection as $className => $collectionName) {
            $classDependencies = $dependencies->get($className);

            foreach ($collections->get($collectionName) as $collectedClass) {
                $classDependencies->add($collectedClass);
            }
        }

        return new SingletonDependencyMap($dependencies, $providers);
    }

    /**
     * @return Generator<class-string>
     */
    private function getClassConstructorParameters(ReflectionClass $reflectionClass): Generator
    {
        foreach (new ConstructorParametersIterator($reflectionClass) as $constructorArgument) {
            $type = $constructorArgument->getType();

            if (($type instanceof ReflectionNamedType) && !$type->isBuiltin()) {
                yield $type->getName();
            }
        }
    }

    /**
     * @return Set<class-string>
     */
    private function topologicalSort(SingletonDependencyMap $singletonDependencyMap): Set
    {
        $dependencyMap = $singletonDependencyMap->dependencyMap;

        /**
         * Keep track of in-degrees for each ReflectionClass.
         *
         * @var Map<class-string,int> $inDegree
         */
        $inDegree = new Map();

        /**
         * The final sorted order
         *
         * @var Set<class-string> $result
         */
        $result = new Set();

        // Initialize in-degrees
        foreach ($dependencyMap->keys() as $dependencyClass) {
            $inDegree->put($dependencyClass, 0);
        }

        // Calculate in-degrees
        foreach ($dependencyMap as $dependencies) {
            foreach ($dependencies as $dependencyClass) {
                if (!$inDegree->hasKey($dependencyClass)) {
                    throw new LogicException('Dependency class has no provider: '.$dependencyClass);
                }

                $inDegree->put($dependencyClass, $inDegree->get($dependencyClass) + 1);
            }
        }

        /**
         * @var Queue<class-string> $queue
         */
        $queue = new Queue();

        // Add vertices with in-degree 0 to the queue
        foreach ($inDegree as $dependencyClass => $degree) {
            if (0 === $degree) {
                $queue->push($dependencyClass);
            }
        }

        while (!$queue->isEmpty()) {
            $current = $queue->pop();
            $result->add($current);

            foreach ($dependencyMap->get($current) as $dependencyClass) {
                $inDegree->put($dependencyClass, $inDegree->get($dependencyClass) - 1);

                if (0 === $inDegree->get($dependencyClass)) {
                    $queue->push($dependencyClass);
                }
            }
        }

        if ($result->count() !== $inDegree->count()) {
            throw new LogicException('Topological sort cycle.');
        }

        return $result;
    }
}
