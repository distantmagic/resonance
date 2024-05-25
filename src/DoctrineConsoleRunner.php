<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Symfony\Component\Console\Application;

final readonly class DoctrineConsoleRunner
{
    public static function run(DependencyInjectionContainer $container): never
    {
        $container->call(static function (
            CoroutineDriverInterface $coroutineDriver,
            DoctrineConsoleEntityManagerProvider $entityManagerProvider,
        ): never {
            $cli = new Application('Doctrine Command Line Interface');

            $cli->setAutoExit(false);
            $cli->setCatchExceptions(true);

            ConsoleRunner::addCommands($cli, $entityManagerProvider);

            $errorCode = $coroutineDriver->run(static function () use ($cli): int {
                return $cli->run();
            });

            exit($errorCode);
        });
    }

    /**
     * This class is used to namespace a few functions.
     */
    private function __construct() {}
}
