<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CommandLoader;
use Distantmagic\Resonance\ConsoleApplication;
use Distantmagic\Resonance\DependencyInjectionContainer;
use Distantmagic\Resonance\DoctrineConsoleEntityManagerProvider;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<ConsoleApplication>
 */
#[Singleton(provides: ConsoleApplication::class)]
final readonly class ConsoleApplicationProvider extends SingletonProvider
{
    public function __construct(
        private DependencyInjectionContainer $container,
        private DoctrineConsoleEntityManagerProvider $doctrineConsoleEntityManagerProvider,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ConsoleApplication
    {
        $consoleApplication = new ConsoleApplication();
        $consoleApplication->setAutoExit(false);
        $consoleApplication->setCommandLoader(new CommandLoader(
            $this->container,
            $phpProjectFiles,
        ));
        $consoleApplication->setName('Resonance');

        return $consoleApplication;
    }
}
