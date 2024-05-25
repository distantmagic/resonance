<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\StaticPageProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'static-pages:build',
    description: 'Generate static pages'
)]
#[WantsFeature(Feature::StaticPages)]
final class StaticPagesBuild extends Command
{
    public function __construct(
        private readonly CoroutineDriverInterface $coroutineDriver,
        private readonly StaticPageProcessor $staticPageProcessor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->coroutineDriver->run(function () {
            $this->staticPageProcessor->process();
        });

        return Command::SUCCESS;
    }
}
