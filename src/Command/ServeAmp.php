<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\AmpServer;
use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'serve:amp',
    description: 'Start combined HTTP and WebSocket server'
)]
final class ServeAmp extends Command
{
    public function __construct(private readonly AmpServer $ampServer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return (int) !$this->ampServer->start();
    }
}
