<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CoroutineCommand extends SymfonyCommand
{
    abstract protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int;

    /**
     * Do not use Symfony's command constructor arguments to make it easier on
     * the DI.
     */
    public function __construct(
        private readonly SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        swoole_async_set([
            'enable_coroutine' => true,
            'log_level' => $this->swooleConfiguration->logLevel,
        ]);

        return SwooleCoroutineHelper::mustRun(function () use ($input, $output): int {
            return $this->executeInCoroutine($input, $output);
        });
    }
}
