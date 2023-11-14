<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Defuse\Crypto\Key;
use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'generate:defuse-key',
    description: 'Generate Defuse Key for OAuth2'
)]
final class GenerateDefuseKey extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = Key::createNewRandomKey();

        $output->writeln($key->saveToAsciiSafeString());

        return Command::SUCCESS;
    }
}
