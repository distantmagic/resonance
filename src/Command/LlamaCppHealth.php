<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\LlamaCppClientInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'llamacpp:health',
    description: 'Get server\'s health status'
)]
final class LlamaCppHealth extends CoroutineCommand
{
    public function __construct(
        CoroutineDriverInterface $coroutineDriver,
        private readonly LlamaCppClientInterface $llamaCppClient,
    ) {
        parent::__construct($coroutineDriver);
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->llamaCppClient->getHealth()->value);

        return Command::SUCCESS;
    }
}
