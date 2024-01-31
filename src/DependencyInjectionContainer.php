<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Distantmagic\Resonance\DependencyInjectionContainerException\AmbiguousProvider;
use Distantmagic\Resonance\DependencyInjectionContainerException\DisabledFeatureProvider;
use Distantmagic\Resonance\DependencyInjectionContainerException\MissingProvider;
use Ds\Map;
use Ds\Set;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use RuntimeException;
use Swoole\Event;

readonly class DependencyInjectionContainer
{
    public PHPProjectFiles $phpProjectFiles;

    /**
     * @var Map<SingletonCollectionInterface,Set<DependencyProvider>>
     */
    private Map $collections;

    /**
     * @var Map<class-string,DependencyProvider>
     */
    private Map $dependencyProviders;

    private SingletonContainer $singletons;

    /**
     * @var Set<FeatureInterface>
     */
    private Set $wantedFeatures;

    public function __construct()
    {
        $this->collections = new Map();
        $this->dependencyProviders = new Map();
        $this->phpProjectFiles = new PHPProjectFiles();
        $this->wantedFeatures = new Set();

        $this->singletons = new SingletonContainer();
        $this->singletons->set(static::class, $this);
    }

    /**
     * @template TReturnType
     *
     * @param Closure(...mixed):TReturnType $function
     *
     * @psalm-suppress PossiblyUnusedReturnValue it's used in apps
     *
     * @return TReturnType
     */
    public function call(Closure $function): mixed
    {
        /**
         * @var null|array<string,mixed>
         */
        $parameters = SwooleCoroutineHelper::mustRun(function () use ($function): array {
            $reflectionFunction = new ReflectionFunction($function);

            return $this->makeParameters($reflectionFunction, new DependencyStack());
        });

        if (!is_array($parameters)) {
            throw new DependencyInjectionContainerException('Unable to build function parameters');
        }

        $ret = $function(...$parameters);

        Event::wait();

        return $ret;
    }

    public function enableFeature(FeatureInterface $feature): void
    {
        $this->wantedFeatures->add($feature);
    }

    /**
     * @template TSingleton as object
     *
     * @param class-string<TSingleton> $className
     *
     * @return TSingleton
     */
    public function make(string $className): object
    {
        $ret = SwooleCoroutineHelper::mustRun(function () use ($className): object {
            $stack = new DependencyStack();

            if ($this->dependencyProviders->hasKey($className)) {
                return $this->makeSingleton($className, $stack);
            }

            return $this->makeClass($className, $stack);
        });

        return $this->assertClass($className, $ret);
    }

    public function registerSingletons(): void
    {
        foreach (new DependencyProviderIterator($this->phpProjectFiles) as $providedClassName => $dependencyProvider) {
            if ($this->dependencyProviders->hasKey($providedClassName)) {
                throw new AmbiguousProvider($providedClassName);
            }

            if ($dependencyProvider->wantsFeature) {
                $this->enableFeature($dependencyProvider->wantsFeature);
            }

            $this->dependencyProviders->put($providedClassName, $dependencyProvider);

            if ($dependencyProvider->collection) {
                if (!$this->collections->hasKey($dependencyProvider->collection)) {
                    $this->collections->put($dependencyProvider->collection, new Set());
                }

                $this->collections->get($dependencyProvider->collection)->add($dependencyProvider);
            }
        }
    }

    /**
     * @template TObject
     *
     * @param class-string<TObject> $className
     *
     * @return TObject
     */
    private function assertClass(string $className, mixed $providedObject): object
    {
        if (is_null($providedObject)) {
            throw new DependencyInjectionContainerException(sprintf('Expected: %s, got NULL', $className));
        }

        if (!is_object($providedObject)) {
            throw new DependencyInjectionContainerException(sprintf('Expected: %s, got %s', $className, gettype($providedObject)));
        }

        if ($providedObject instanceof $className) {
            return $providedObject;
        }

        throw new DependencyInjectionContainerException(sprintf(
            'Invalid provided class: %s, expected: %s',
            $providedObject::class,
            $className,
        ));
    }

    private function buildCollection(SingletonCollectionInterface $singletonCollection, DependencyStack $stack): void
    {
        $collection = $this->collections->get($singletonCollection, null);

        if (!$collection) {
            return;
        }

        foreach ($collection as $collectionMember) {
            if ($this->isClassWanted($collectionMember->providedClassName)) {
                $this->makeSingleton(
                    $collectionMember->providedClassName,
                    $stack->branch($collectionMember->providedClassName),
                );
            }
        }
    }

    /**
     * @param class-string $className
     */
    private function isClassWanted(string $className): bool
    {
        $dependencyProvider = $this->dependencyProviders->get($className, null);

        if (is_null($dependencyProvider)) {
            return false;
        }

        return $this->isDependencyProviderWanted($dependencyProvider);
    }

    private function isDependencyProviderWanted(DependencyProvider $dependencyProvider): bool
    {
        if (is_null($dependencyProvider->grantsFeature)) {
            return true;
        }

        return $this->wantedFeatures->contains($dependencyProvider->grantsFeature);
    }

    /**
     * @template TObject as object
     *
     * @param class-string<TObject> $className
     *
     * @return TObject
     */
    private function makeClass(string $className, DependencyStack $stack): object
    {
        return $this->makeClassFromReflection(new ReflectionClass($className), $stack);
    }

    /**
     * @template TObject as object
     *
     * @param ReflectionClass<TObject> $reflectionClass
     *
     * @return TObject
     */
    private function makeClassFromReflection(ReflectionClass $reflectionClass, DependencyStack $stack): object
    {
        $constructorReflection = $reflectionClass->getConstructor();

        if ($constructorReflection) {
            return $reflectionClass->newInstance(...$this->makeParameters($constructorReflection, $stack));
        }

        return $reflectionClass->newInstance();
    }

    /**
     * @return array<string,mixed>
     */
    private function makeParameters(ReflectionFunctionAbstract $reflectionFunction, DependencyStack $stack): array
    {
        /**
         * @var array<string,mixed> $parameters
         */
        $parameters = [];

        foreach (new SingletonFunctionParametersIterator($reflectionFunction) as $name => $singletonFunctionParameter) {
            if ($singletonFunctionParameter->reflectionParameter->isOptional() && !$this->isClassWanted($singletonFunctionParameter->className)) {
                $parameters[$name] = null;
            } else {
                $parameters[$name] = $this->makeSingleton(
                    $singletonFunctionParameter->className,
                    $stack->branch($singletonFunctionParameter->className),
                );
            }
        }

        return $parameters;
    }

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $className
     *
     * @return TSingleton
     */
    private function makeSingleton(string $className, DependencyStack $stack): object
    {
        if ($this->singletons->has($className)) {
            return $this->singletons->get($className);
        }

        $dependencyProvider = $this->dependencyProviders->get($className, null);

        if (!$dependencyProvider) {
            throw new MissingProvider($className, $stack);
        }

        if (!$this->isDependencyProviderWanted($dependencyProvider)) {
            if (!$dependencyProvider->grantsFeature) {
                throw new RuntimeException('Classname with no provider, not wanted: '.$className);
            }

            throw new DisabledFeatureProvider(
                $className,
                $dependencyProvider->grantsFeature,
                $stack,
            );
        }

        foreach ($dependencyProvider->requiredCollections as $singletonCollection) {
            $this->buildCollection($singletonCollection, $stack);
        }

        $potentialSingletonProvider = $this->makeClassFromReflection($dependencyProvider->providerReflectionClass, $stack);

        if ($potentialSingletonProvider instanceof SingletonProviderInterface) {
            $potentialSingleton = $potentialSingletonProvider->provide($this->singletons, $this->phpProjectFiles);
        } else {
            $potentialSingleton = $potentialSingletonProvider;
        }

        $singleton = $this->assertClass($className, $potentialSingleton);

        $this->singletons->set($className, $singleton);

        return $singleton;
    }
}
