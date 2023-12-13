<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\StaticPageProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'static-pages:build',
    description: 'Generate static pages'
)]
final class StaticPagesBuild extends CoroutineCommand
{
    public function __construct(private StaticPageProcessor $staticPageProcessor)
    {
        parent::__construct();
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        $this->staticPageProcessor->process();

        return Command::SUCCESS;
    }
}
