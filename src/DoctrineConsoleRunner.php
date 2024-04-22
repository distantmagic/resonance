<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Swoole\Runtime;
use Symfony\Component\Console\Application;

use function Distantmagic\Resonance\helpers\coroutineMustRun;

final readonly class DoctrineConsoleRunner
{
    public static function run(DependencyInjectionContainer $container): never
    {
        Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

        $container->call(static function (DoctrineConsoleEntityManagerProvider $entityManagerProvider): never {
            $cli = new Application('Doctrine Command Line Interface');

            $cli->setAutoExit(false);
            $cli->setCatchExceptions(true);

            ConsoleRunner::addCommands($cli, $entityManagerProvider);

            $errorCode = coroutineMustRun(static function () use ($cli): int {
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
