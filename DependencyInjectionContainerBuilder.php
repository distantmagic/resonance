<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

use function Swoole\Coroutine\run;

final readonly class DependencyInjectionContainerBuilder
{
    public function __construct(private ?ConsoleOutputInterface $output = null) {}

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
        $this->output?->writeln('container: building services');

        $container = new DependencyInjectionContainer();

        foreach ($this->sortedDependencies($container) as $singletonDependency) {
            // $this->output?->writeln('container: building '.$singletonDependency->className);

            $singleton = $container->make($singletonDependency->resolver);

            $container->singletons->set(
                $singletonDependency->className,
                $singleton instanceof SingletonProviderInterface
                    ? $singleton->provide($container->singletons, $this->output)
                    : $singleton
            );
        }

        $this->output?->writeln('container: ready - starting application');

        return $container;
    }

    private function sortedDependencies(DependencyInjectionContainer $container): SingletonDependencyIterator
    {
        $projectPhpFiles = new PHPFileIterator(DM_APP_ROOT);
        $projectPhpReflections = new PHPFileReflectionClassIterator($projectPhpFiles);
        $singletonAttributes = new PHPFileReflectionClassAttributeIterator($projectPhpReflections, Singleton::class);

        return new SingletonDependencyIterator($container->singletons, $singletonAttributes);
    }
}
