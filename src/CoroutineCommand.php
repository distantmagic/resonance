<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Swoole\Coroutine\run;

abstract class CoroutineCommand extends SymfonyCommand
{
    abstract protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int;

    /**
     * Do not use Symfony's command constructor arguments to make it easier on
     * the DI.
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = 0;

        /**
         * @var bool
         */
        $coroutineResult = run(function () use ($input, $output, &$result) {
            $result = $this->executeInCoroutine($input, $output);
        });

        if (!$coroutineResult) {
            return Command::FAILURE;
        }

        return $result;
    }
}
