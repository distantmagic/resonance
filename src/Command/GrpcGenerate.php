<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Feature;
use Nette\PhpGenerator\Printer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'grpc:generate',
    description: 'Generate GRPC stubs'
)]
#[WantsFeature(Feature::Grpc)]
final class GrpcGenerate extends Command
{
    public function __construct(private readonly Printer $printer)
    {
        parent::__construct();
    }

    protected function configure(): void {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
