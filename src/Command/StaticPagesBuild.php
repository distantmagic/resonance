<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\StaticPageProcessor;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'static-pages:build',
    description: 'Generate static pages'
)]
#[WantsFeature(Feature::StaticPages)]
final class StaticPagesBuild extends CoroutineCommand
{
    public function __construct(
        private readonly StaticPageProcessor $staticPageProcessor,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        $this->staticPageProcessor->process();

        return Command::SUCCESS;
    }
}
