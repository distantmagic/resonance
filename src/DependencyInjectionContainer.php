<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Distantmagic\Resonance\Attribute\OverridesSingletonProvider;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DependencyInjectionContainerException\AmbiguousProvider;
use Distantmagic\Resonance\DependencyInjectionContainerException\DependencyCycle;
use Distantmagic\Resonance\DependencyInjectionContainerException\MissingProvider;
use Ds\Map;
use Ds\Set;
use ReflectionClass;
use ReflectionFunction;
use Swoole\Coroutine\WaitGroup;
use Throwable;

use function Swoole\Coroutine\run;

readonly class DependencyInjectionContainer
{
    public PHPProjectFiles $phpProjectFiles;

    /**
     * @var Map<class-string,Set<SingletonCollectionInterface>>
     */
    private Map $collectionDependencies;

    /**
     * @var Map<SingletonCollectionInterface,Set<class-string>>
     */
    private Map $collections;

    /**
     * @var Set<string>
     */
    private Set $notBuildable;

    /**
     * @var Map<class-string,ReflectionClass>
     */
    private Map $providers;

    private SingletonContainer $singletons;

    public function __construct()
    {
        $this->collectionDependencies = new Map();
        $this->collections = new Map();
        $this->notBuildable = new Set();
        $this->providers = new Map();
        $this->phpProjectFiles = new PHPProjectFiles();

        $this->singletons = new SingletonContainer();
        $this->singletons->set(self::class, $this);
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
        $reflectionFunction = new ReflectionFunction($function);

        $parameters = [];

        foreach (new SingletonFunctionParametersIterator($reflectionFunction) as $name => $functionParameter) {
            $parameters[$name] = $this->make($functionParameter->className);
        }

        return $function(...$parameters);
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
        /**
         * @var null|TSingleton
         */
        $ret = null;

        /**
         * Bringing this reference out of the coroutine event loops allows the
         * console component to catch that exception and format it.
         *
         * @var null|Throwable
         */
        $exception = null;

        /**
         * WaitGroup is not necesary here since `run` is going to wait for all
         * coroutines to finish.
         *
         * @var bool $coroutineResult
         */
        $coroutineResult = run(function () use ($className, &$exception, &$ret) {
            try {
                $ret = $this->doMake($className);
            } catch (Throwable $throwable) {
                $exception = $throwable;
            }
        });

        if ($exception) {
            throw $exception;
        }

        if (!$coroutineResult) {
            throw new DependencyInjectionContainerException(
                message: 'Container event loop failed',
            );
        }

        if (!($ret instanceof $className)) {
            throw new DependencyInjectionContainerException(
                message: 'Unable to make an instance of '.$className,
            );
        }

        return $ret;
    }

    public function registerSingletons(): void
    {
        foreach ($this->phpProjectFiles->findByAttribute(Singleton::class) as $reflectionAttribute) {
            $providedClassName = $reflectionAttribute->attribute->provides ?? $reflectionAttribute->reflectionClass->getName();

            foreach ($reflectionAttribute->reflectionClass->getAttributes(OverridesSingletonProvider::class) as $overrides) {
                $overridesClassName = $overrides->newInstance()->overrides;

                if ($this->providers->get($providedClassName)->getName() === $overridesClassName) {
                    $this->providers->remove($providedClassName);
                } else {
                    throw new DependencyInjectionContainerException(sprintf(
                        'Overridden provider is not registered: "%s"',
                        $overridesClassName,
                    ));
                }
            }

            if ($this->providers->hasKey($providedClassName)) {
                throw new AmbiguousProvider(
                    $providedClassName,
                    [
                        $this->providers->get($providedClassName)->getName(),
                        $reflectionAttribute->reflectionClass->getName(),
                    ]
                );
            }

            $collectionName = $reflectionAttribute->attribute->collection;

            if ($collectionName) {
                $this->addToCollection($collectionName, $providedClassName);
            }

            foreach ($reflectionAttribute->reflectionClass->getAttributes(RequiresSingletonCollection::class) as $requiresCollectionReflection) {
                $requiredCollection = $requiresCollectionReflection->newInstance()->collection;

                if ($requiredCollection instanceof SingletonCollectionInterface) {
                    $this->addCollectionDependency($providedClassName, $requiredCollection);
                }
            }

            $this->providers->put($providedClassName, $reflectionAttribute->reflectionClass);
        }
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
     * @param Set<class-string> $previous
     *
     * @return Map<string,null|object>
     */
    private function buildSingletonParameters(ReflectionClass $reflectionClass, bool $isNullable, Set $previous): Map
    {
        /**
         * @var Map<string,null|object> $parameters
         */
        $parameters = new Map();

        foreach (new SingletonConstructorParametersIterator($reflectionClass) as $name => $functionParameter) {
            $parameters->put(
                $name,
                $this->makeSingleton(
                    $functionParameter->className,
                    $functionParameter->reflectionParameter->allowsNull(),
                    $previous->copy(),
                ),
            );
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
    private function doMake(string $className): object
    {
        if ($this->singletons->has($className)) {
            return $this->singletons->get($className);
        }

        if ($this->providers->hasKey($className)) {
            return $this->makeSingleton($className, false, new Set());
        }

        $reflectionClass = new ReflectionClass($className);

        $parameters = $this->buildSingletonParameters($reflectionClass, false, new Set());

        return $reflectionClass->newInstance(...$parameters->toArray());
    }

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $className
     * @param Set<class-string>        $previous
     *
     * @return null|TSingleton
     */
    private function doMakeSingleton(string $className, bool $isNullable, Set $previous): ?object
    {
        if (!$this->providers->hasKey($className)) {
            throw new MissingProvider($className, $previous);
        }

        if ($previous->contains($className)) {
            throw new DependencyCycle($className, $previous);
        }

        $previous->add($className);

        $providerReflection = $this->providers->get($className);

        if ($this->collectionDependencies->hasKey($className)) {
            $collectionDependencies = $this->collectionDependencies->get($className);

            foreach ($collectionDependencies as $collectionName) {
                if ($this->collections->hasKey($collectionName)) {
                    foreach ($this->collections->get($collectionName) as $collectionClassName) {
                        $this->makeSingleton($collectionClassName, false, $previous->copy());
                    }
                }
            }
        }

        $parameters = $this->buildSingletonParameters($providerReflection, $isNullable, $previous);

        $provider = $providerReflection->newInstance(...$parameters->toArray());

        if ($provider instanceof SingletonProviderInterface) {
            if (!$provider->shouldRegister()) {
                if ($isNullable) {
                    return null;
                }

                throw new MissingProvider($className, $previous);
            }

            /**
             * @var TSingleton
             */
            return $provider->provide($this->singletons, $this->phpProjectFiles);
        }

        /**
         * @var null|TSingleton
         */
        return $provider;
    }

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $className
     * @param Set<class-string>        $previous
     *
     * @return null|TSingleton
     */
    private function makeSingleton(string $className, bool $isNullable, Set $previous): ?object
    {
        if ($this->notBuildable->contains($className) && $isNullable) {
            return null;
        }

        if ($this->singletons->has($className)) {
            return $this->singletons->get($className);
        }

        try {
            $singleton = $this->doMakeSingleton($className, $isNullable, $previous);

            if (!$singleton) {
                $this->notBuildable->add($className);

                if ($isNullable) {
                    return null;
                }

                throw new MissingProvider($className, $previous);
            }

            $this->singletons->set($className, $singleton);

            return $singleton;
        } catch (Throwable $throwable) {
            throw new DependencyInjectionContainerException(
                message: 'Error while building: '.$className,
                previous: $throwable,
            );
        }
    }
}
