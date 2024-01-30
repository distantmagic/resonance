<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SwooleServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'serve',
    description: 'Start combined HTTP and WebSocket server'
)]
#[WantsFeature(Feature::TaskServer)]
final class Serve extends Command
{
    public function __construct(private SwooleServer $swooleServer) {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return (int) !$this->swooleServer->start();
    }
}
