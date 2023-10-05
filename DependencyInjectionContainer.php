<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Set;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use Resonance\Attribute\Singleton;

readonly class DependencyInjectionContainer
{
    /**
     * @var Map<class-string,Set<SingletonCollectionInterface>>
     */
    private Map $collectionDependencies;

    /**
     * @var Map<SingletonCollectionInterface,Set<class-string>>
     */
    private Map $collections;

    /**
     * @var Map<class-string,ReflectionClass>
     */
    private Map $providers;

    private SingletonContainer $singletons;

    public function __construct()
    {
        $this->collectionDependencies = new Map();
        $this->collections = new Map();
        $this->providers = new Map();
        $this->singletons = new SingletonContainer();
    }

    public function indexDirectory(string $dirname): void
    {
        $files = new PHPFileIterator($dirname);
        $reflections = new PHPFileReflectionClassIterator($files);
        $attributes = new PHPFileReflectionClassAttributeIterator($reflections, Singleton::class);

        foreach ($attributes as $reflectionAttribute) {
            $providedClassName = $reflectionAttribute->attribute->provides ?? $reflectionAttribute->reflectionClass->getName();

            $this->providers->put($providedClassName, $reflectionAttribute->reflectionClass);

            $collectionName = $reflectionAttribute->attribute->collection;

            if ($collectionName) {
                $this->addToCollection($collectionName, $providedClassName);
            }

            if ($reflectionAttribute->attribute->requiresCollection) {
                $this->addCollectionDependency($providedClassName, $reflectionAttribute->attribute->requiresCollection);
            }
        }
    }

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $className
     *
     * @return TSingleton
     */
    public function make(string $className): object
    {
        if ($this->singletons->has($className)) {
            return $this->singletons->get($className);
        }

        $reflectionClass = new ReflectionClass($className);
        $constructorReflection = $reflectionClass->getConstructor();

        $parameters = [];

        if ($constructorReflection) {
            foreach ($constructorReflection->getParameters() as $constructorParameter) {
                $type = $constructorParameter->getType();

                if (!($type instanceof ReflectionNamedType)) {
                    throw new LogicException('Not a named type: '.$type::class);
                }

                $parameters[$constructorParameter->getName()] = $this->makeSingleton($type->getName(), new Set());
            }
        }

        return new $className(...$parameters);
    }

    /**
     * @param class-string $className
     */
    private function addCollectionDependency(string $className, SingletonCollectionInterface $collectionName): void
    {
        if (!$this->collectionDependencies->hasKey($className)) {
            $this->collectionDependencies->put($className, new Set());
        }

        $this->collectionDependencies->get($className)->add($collectionName);
    }

    /**
     * @param class-string $providedClassName
     */
    private function addToCollection(SingletonCollectionInterface $collectionName, string $providedClassName): void
    {
        if (!$this->collections->hasKey($collectionName)) {
            $this->collections->put($collectionName, new Set());
        }

        $this->collections->get($collectionName)->add($providedClassName);
    }

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $className
     * @param Set<class-string>        $previous
     *
     * @return TSingleton
     */
    private function doMakeSingleton(string $className, Set $previous): object
    {
        if (!$this->providers->hasKey($className)) {
            throw new LogicException('There is no singleton provider registered for: '.$className);
        }

        if ($previous->contains($className)) {
            throw new LogicException('Dependency injection cycle.');
        }

        $previous->add($className);

        $providerReflection = $this->providers->get($className);
        $constructorReflection = $providerReflection->getConstructor();

        $parameters = [];

        if ($constructorReflection) {
            foreach ($constructorReflection->getParameters() as $constructorParameter) {
                $type = $constructorParameter->getType();

                if (!($type instanceof ReflectionNamedType)) {
                    throw new LogicException('Not a named type: '.$type::class);
                }

                $parameters[$constructorParameter->getName()] = $this->makeSingleton($type->getName(), $previous);
            }
        }

        $collectionDependencies = $this->collectionDependencies->get($className, new Set());

        foreach ($collectionDependencies as $collectionName) {
            if ($this->collections->hasKey($collectionName)) {
                foreach ($this->collections->get($collectionName) as $collectionClassName) {
                    $this->makeSingleton($collectionClassName, new Set());
                }
            }
        }

        $provider = $providerReflection->newInstance(...$parameters);

        if ($provider instanceof SingletonProviderInterface) {
            return $provider->provide($this->singletons);
        }

        return $provider;
    }

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $className
     * @param Set<class-string>        $previous
     *
     * @return TSingleton
     */
    private function makeSingleton(string $className, Set $previous): object
    {
        if ($this->singletons->has($className)) {
            return $this->singletons->get($className);
        }

        $singleton = $this->doMakeSingleton($className, $previous);

        $this->singletons->set($className, $singleton);

        return $singleton;
    }
}
