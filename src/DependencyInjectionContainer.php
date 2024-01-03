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
use Throwable;

use function Swoole\Coroutine\run;

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

    /**
     * @var Map<class-string,FeatureInterface>
     */
    private Map $disabledFeatureProviders;

    private SingletonContainer $singletons;

    /**
     * @var Set<FeatureInterface>
     */
    private Set $wantedFeatures;

    public function __construct()
    {
        $this->collections = new Map();
        $this->dependencyProviders = new Map();
        $this->disabledFeatureProviders = new Map();
        $this->phpProjectFiles = new PHPProjectFiles();
        $this->wantedFeatures = new Set();

        $this->singletons = new SingletonContainer();
        $this->singletons->set($this::class, $this);
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
         * Bringing this reference out of the coroutine event loops allows the
         * console component to catch that exception and format it.
         *
         * @var null|Throwable
         */
        $exception = null;

        /**
         * @var null|array<string,mixed>
         */
        $parameters = null;

        /**
         * @var bool $coroutineResult
         */
        $coroutineResult = run(function () use (&$exception, $function, &$parameters) {
            try {
                $reflectionFunction = new ReflectionFunction($function);

                $parameters = $this->makeParameters($reflectionFunction, new DependencyStack());
            } catch (Throwable $throwable) {
                $exception = $throwable;
            }
        });

        if ($exception) {
            throw $exception;
        }

        if (!$coroutineResult) {
            throw new DependencyInjectionContainerException('Unable to start event loop');
        }

        if (!is_array($parameters)) {
            throw new DependencyInjectionContainerException('Unable to build function parameters');
        }

        return $function(...$parameters);
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
        /**
         * @var null|Throwable
         */
        $exception = null;

        /**
         * @var null|TSingleton
         */
        $ret = null;

        /**
         * @var bool $coroutineResult
         */
        $coroutineResult = run(function () use ($className, &$exception, &$ret) {
            $stack = new DependencyStack();

            try {
                if ($this->dependencyProviders->hasKey($className)) {
                    $ret = $this->makeSingleton($className, $stack);
                } else {
                    $ret = $this->makeClass($className, $stack);
                }
            } catch (Throwable $throwable) {
                $exception = $throwable;
            }
        });

        if ($exception) {
            throw $exception;
        }

        if (!$coroutineResult) {
            throw new DependencyInjectionContainerException('Unable to start event loop');
        }

        return $this->assertClass($className, $ret);
    }

    public function registerSingletons(): void
    {
        foreach (new DependencyProviderIterator($this->phpProjectFiles) as $providedClassName => $dependencyProvider) {
            if ($this->dependencyProviders->hasKey($providedClassName)) {
                throw new AmbiguousProvider($providedClassName);
            }

            if ($dependencyProvider->wantsFeature) {
                $this->wantedFeatures->add($dependencyProvider->wantsFeature);
            }

            $this->dependencyProviders->put($providedClassName, $dependencyProvider);
        }

        foreach ($this->dependencyProviders as $providedClassName => $dependencyProvider) {
            if ($dependencyProvider->grantsFeature && !$this->wantedFeatures->contains($dependencyProvider->grantsFeature)) {
                $this->disabledFeatureProviders->put($providedClassName, $dependencyProvider->grantsFeature);
            } elseif ($dependencyProvider->collection) {
                if (!$this->collections->hasKey($dependencyProvider->collection)) {
                    $this->collections->put($dependencyProvider->collection, new Set());
                }

                $this->collections->get($dependencyProvider->collection)->add($dependencyProvider);
            }
        }

        foreach ($this->disabledFeatureProviders->keys() as $providedClassName) {
            $this->dependencyProviders->remove($providedClassName);
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

        if (is_a($providedObject, $className, true)) {
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
            $this->makeSingleton(
                $collectionMember->providedClassName,
                $stack->branch($collectionMember->providedClassName),
            );
        }
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
            if ($singletonFunctionParameter->reflectionParameter->isOptional() && !$this->dependencyProviders->hasKey($singletonFunctionParameter->className)) {
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
            if ($this->disabledFeatureProviders->hasKey($className)) {
                throw new DisabledFeatureProvider(
                    $className,
                    $this->disabledFeatureProviders->get($className),
                    $stack,
                );
            }

            throw new MissingProvider($className, $stack);
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
