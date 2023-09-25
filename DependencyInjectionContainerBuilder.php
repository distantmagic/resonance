<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use Resonance\Attribute\Singleton;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

use function Swoole\Coroutine\run;

final readonly class DependencyInjectionContainerBuilder
{
    public function __construct() {}

    public function buildContainer(): DependencyInjectionContainer
    {
        /**
         * @var null|DependencyInjectionContainer $container
         */
        $container = null;

        /**
         * @var bool $coroutineResult
         */
        $coroutineResult = run(function () use (&$container) {
            $container = $this->doBuildContainer();
        });

        if (!$coroutineResult || !($container instanceof DependencyInjectionContainer)) {
            throw new RuntimeException('Unable to create container.');
        }

        return $container;
    }

    private function doBuildContainer(): DependencyInjectionContainer
    {
        $container = new DependencyInjectionContainer();

        foreach ($this->sortedDependencies($container) as $singletonDependency) {
            $singleton = $container->make($singletonDependency->resolver);

            $container->singletons->set(
                $singletonDependency->className,
                $singleton instanceof SingletonProviderInterface
                    ? $singleton->provide($container->singletons)
                    : $singleton
            );
        }

        return $container;
    }

    /**
     * @return Generator<SplFileInfo>
     */
    private function phpFiles(): Generator
    {
        foreach (new PHPFileIterator(DM_RESONANCE_ROOT) as $fileInfo) {
            yield $fileInfo;
        }

        foreach (new PHPFileIterator(DM_APP_ROOT) as $fileInfo) {
            yield $fileInfo;
        }
    }

    private function sortedDependencies(DependencyInjectionContainer $container): SingletonResolverClassNameIterator
    {
        $projectPhpReflections = new PHPFileReflectionClassIterator($this->phpFiles());
        $singletonAttributes = new PHPFileReflectionClassAttributeIterator($projectPhpReflections, Singleton::class);

        return new SingletonResolverClassNameIterator($container->singletons, $singletonAttributes);
    }
}
