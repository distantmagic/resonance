<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;
use Ds\Set;
use LogicException;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;
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
     * @var Map<class-string,ReflectionClass>
     */
    private Map $providers;

    private SingletonContainer $singletons;

    /**
     * @template TReturnType
     *
     * @param Closure(...mixed):TReturnType $function
     *
     * @return TReturnType
     *
     * @psalm-suppress PossiblyUnusedReturnValue it's used in config files
     */
    public static function boot(Closure $function): mixed
    {
        return self::fromGlobals()->call($function);
    }

    public static function fromGlobals(): self
    {
        $container = new self();
        $container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
        $container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
        $container->registerSingletons();

        return $container;
    }

    public function __construct()
    {
        $this->collectionDependencies = new Map();
        $this->collections = new Map();
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
     * @return TReturnType
     */
    public function call(Closure $function): mixed
    {
        $reflectionFunction = new ReflectionFunction($function);

        $parameters = [];

        foreach (new SingletonFunctionParametersIterator($reflectionFunction) as $name => $typeClassName) {
            $parameters[$name] = $this->make($typeClassName);
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
         * WaitGroup is not necesary here since `run` is going to wait for all
         * coroutines to finish.
         *
         * @var bool $coroutineResult
         */
        $coroutineResult = run(function () use ($className, &$ret) {
            $ret = $this->doMake($className);
        });

        if (!$coroutineResult) {
            throw new RuntimeException('Container event loop failed. Tried to build: '.$className);
        }

        if (!($ret instanceof $className)) {
            throw new LogicException('Unable to make an instance of '.$className);
        }

        return $ret;
    }

    public function registerSingletons(): void
    {
        foreach ($this->phpProjectFiles->findByAttribute(Singleton::class) as $reflectionAttribute) {
            $providedClassName = $reflectionAttribute->attribute->provides ?? $reflectionAttribute->reflectionClass->getName();
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
            $this->makeSingleton($className, new Set());

            return $this->singletons->get($className);
        }

        $reflectionClass = new ReflectionClass($className);

        /**
         * @var Map<string,object> $parameters
         */
        $parameters = new Map();

        foreach (new SingletonConstructorParametersIterator($reflectionClass) as $name => $typeClassName) {
            $parameters->put(
                $name,
                $this->makeSingleton($typeClassName, new Set()),
            );
        }

        return $reflectionClass->newInstance(...$parameters->toArray());
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
            throw new LogicException(sprintf(
                "No singleton provider registered for:\n-> %s\n-> %s\n",
                $previous->join("\n-> "),
                $className,
            ));
        }

        if ($previous->contains($className)) {
            throw new LogicException(sprintf(
                "Dependency injection cycle:\n-> %s\n-> %s\n",
                $previous->join("\n-> "),
                $className,
            ));
        }

        $previous->add($className);

        $providerReflection = $this->providers->get($className);

        if ($this->collectionDependencies->hasKey($className)) {
            $collectionDependencies = $this->collectionDependencies->get($className);

            foreach ($collectionDependencies as $collectionName) {
                if ($this->collections->hasKey($collectionName)) {
                    foreach ($this->collections->get($collectionName) as $collectionClassName) {
                        $this->makeSingleton($collectionClassName, $previous->copy());
                    }
                }
            }
        }

        $parameters = [];

        foreach (new SingletonConstructorParametersIterator($providerReflection) as $name => $typeClassName) {
            $parameters[$name] = $this->makeSingleton($typeClassName, $previous);
        }

        $provider = $providerReflection->newInstance(...$parameters);

        if ($provider instanceof SingletonProviderInterface) {
            /**
             * @var TSingleton
             */
            return $provider->provide($this->singletons, $this->phpProjectFiles);
        }

        /**
         * @var TSingleton
         */
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

        try {
            $singleton = $this->doMakeSingleton($className, $previous);

            $this->singletons->set($className, $singleton);

            return $singleton;
        } catch (Throwable $throwable) {
            throw new LogicException(
                message: 'Error while building: '.$className,
                previous: $throwable,
            );
        }
    }
}
