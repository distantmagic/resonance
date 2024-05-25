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
    name: 'serve:swoole',
    description: 'Start combined HTTP and WebSocket server'
)]
#[WantsFeature(Feature::SwooleTaskServer)]
final class ServeSwoole extends Command
{
    public function __construct(private readonly SwooleServer $swooleServer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return (int) !$this->swooleServer->start();
    }
}
