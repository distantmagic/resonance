<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\DependencyInjectionContainerException\AmbiguousProvider;
use Distantmagic\Resonance\DependencyInjectionContainerException\DisabledFeatureProvider;
use Distantmagic\Resonance\DependencyInjectionContainerException\MissingPhpExtensions;
use Distantmagic\Resonance\DependencyInjectionContainerException\MissingProvider;
use Ds\Map;
use Ds\Set;
use LogicException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Swoole\Event;

use function Distantmagic\Resonance\helpers\coroutineMustRun;

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
     * @var Set<DependencyProvider<SideEffectProviderInterface>>
     */
    private Set $executedSideEffects;

    /**
     * @var Map<FeatureInterface,Set<DependencyProvider<SideEffectProviderInterface>>>
     */
    private Map $sideEffects;

    private SingletonContainer $singletons;

    /**
     * @var Set<FeatureInterface>
     */
    private Set $wantedFeatures;

    public function __construct()
    {
        $this->collections = new Map();
        $this->dependencyProviders = new Map();
        $this->executedSideEffects = new Set();
        $this->phpProjectFiles = new PHPProjectFiles();
        $this->sideEffects = new Map();
        $this->wantedFeatures = new Set();

        $this->singletons = new SingletonContainer();
        $this->singletons->set(static::class, $this);
    }

    /**
     * @template TReturnType
     *
     * @param Closure(...mixed):TReturnType $function
     *
     * @psalm-suppress PossiblyUnusedReturnValue used in applications
     *
     * @return TReturnType
     */
    public function call(Closure $function): mixed
    {
        /**
         * @var null|array<string,mixed>
         */
        $parameters = coroutineMustRun(function () use ($function): array {
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

        if (!$this->sideEffects->hasKey($feature)) {
            return;
        }

        foreach ($this->sideEffects->get($feature) as $sideEffectDependencyProvider) {
            if (!$this->executedSideEffects->contains($sideEffectDependencyProvider)) {
                $this
                    ->makeSingleton($sideEffectDependencyProvider->providedClassName, new DependencyStack())
                    ->provideSideEffect()
                ;

                $this->executedSideEffects->add($sideEffectDependencyProvider);
            }
        }
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
        $ret = coroutineMustRun(function () use ($className): object {
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

            foreach ($dependencyProvider->wantsFeatures as $wantedFeature) {
                $this->enableFeature($wantedFeature);
            }

            $this->dependencyProviders->put($providedClassName, $dependencyProvider);

            if ($dependencyProvider->collection) {
                if (!$this->collections->hasKey($dependencyProvider->collection)) {
                    $this->collections->put($dependencyProvider->collection, new Set());
                }

                $this->collections->get($dependencyProvider->collection)->add($dependencyProvider);
            }

            if ($dependencyProvider->sideEffect) {
                if (!is_a($dependencyProvider->providedClassName, SideEffectProviderInterface::class, true)) {
                    throw new LogicException(sprintf(
                        'Side effect provider must implement "%s" interface',
                        SideEffectProviderInterface::class,
                    ));
                }

                if (!$this->sideEffects->hasKey($dependencyProvider->sideEffect->feature)) {
                    $this->sideEffects->put($dependencyProvider->sideEffect->feature, new Set());
                }

                /**
                 * @var DependencyProvider<SideEffectProviderInterface> $dependencyProvider
                 */
                $this->sideEffects->get($dependencyProvider->sideEffect->feature)->add($dependencyProvider);
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
        if ($dependencyProvider->grantsFeatures->isEmpty()) {
            return true;
        }

        foreach ($dependencyProvider->grantsFeatures as $grantedFeature) {
            if (!$this->wantedFeatures->contains($grantedFeature)) {
                return false;
            }
        }

        return true;
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
        $reflectionClassAttributeManager = new ReflectionAttributeManager($reflectionClass);
        $requiredPhpExtensions = $reflectionClassAttributeManager->findAttributes(RequiresPhpExtension::class);

        if (!$requiredPhpExtensions->isEmpty()) {
            /**
             * @var list<non-empty-string>
             */
            $missingExtensions = [];

            foreach ($requiredPhpExtensions as $requiredPhpExtension) {
                if (!extension_loaded($requiredPhpExtension->name)) {
                    $missingExtensions[] = $requiredPhpExtension->name;
                }
            }

            if (!empty($missingExtensions)) {
                throw new MissingPhpExtensions($reflectionClass->name, $missingExtensions);
            }
        }

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
            throw new DisabledFeatureProvider(
                $className,
                $dependencyProvider->grantsFeatures->diff($this->wantedFeatures),
                $stack,
            );
        }

        foreach ($dependencyProvider->requiredCollections as $singletonCollection) {
            $this->buildCollection($singletonCollection, $stack);
        }

        $potentialSingletonProvider = $this->makeClassFromReflection($dependencyProvider->providerReflectionClass, $stack);

        if ($potentialSingletonProvider instanceof SingletonProviderInterface) {
            if ($potentialSingletonProvider instanceof RegisterableInterface && !$potentialSingletonProvider->shouldRegister()) {
                throw new DependencyInjectionContainerException(sprintf(
                    '"%s" service provider refused to register',
                    $potentialSingletonProvider::class,
                ));
            }

            $potentialSingleton = $potentialSingletonProvider->provide($this->singletons, $this->phpProjectFiles);
        } else {
            $potentialSingleton = $potentialSingletonProvider;
        }

        $singleton = $this->assertClass($className, $potentialSingleton);

        $this->singletons->set($className, $singleton);

        return $singleton;
    }
}
