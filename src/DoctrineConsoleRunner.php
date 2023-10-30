<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Swoole\Runtime;
use Symfony\Component\Console\Application;

use function Swoole\Coroutine\run;

final readonly class DoctrineConsoleRunner
{
    public static function run(): never
    {
        Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

        DependencyInjectionContainer::boot(static function (
            DoctrineConsoleEntityManagerProvider $entityManagerProvider,
        ) {
            $cli = new Application('Doctrine Command Line Interface');

            $cli->setAutoExit(false);
            $cli->setCatchExceptions(true);

            ConsoleRunner::addCommands($cli, $entityManagerProvider);

            /**
             * @var bool
             */
            $coroutineResult = run(static function () use ($cli) {
                $cli->run();
            });

            exit((int) !$coroutineResult);
        });
    }

    /**
     * This class is used to namespace a few functions.
     */
    private function __construct() {}
}
