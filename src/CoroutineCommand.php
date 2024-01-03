<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Swoole\Coroutine\run;

abstract class CoroutineCommand extends SymfonyCommand
{
    abstract protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int;

    /**
     * Do not use Symfony's command constructor arguments to make it easier on
     * the DI.
     */
    public function __construct(
        private SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        swoole_async_set([
            'enable_coroutine' => true,
            'log_level' => $this->swooleConfiguration->logLevel,
        ]);

        /**
         * @var null|Throwable
         */
        $exception = null;

        $result = 0;

        /**
         * @var bool
         */
        $coroutineResult = run(function () use (&$exception, $input, $output, &$result) {
            try {
                $result = $this->executeInCoroutine($input, $output);
            } catch (Throwable $throwable) {
                $exception = $throwable;
            }
        });

        if ($exception) {
            throw $exception;
        }

        if (!$coroutineResult) {
            return Command::FAILURE;
        }

        return $result;
    }
}
