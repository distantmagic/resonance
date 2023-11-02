<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\StaticPageProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Swoole\Coroutine\run;

#[ConsoleCommand(
    name: 'static-pages:build',
    description: 'Generate static pages'
)]
final class StaticPagesBuild extends Command
{
    public function __construct(private StaticPageProcessor $staticPageProcessor)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var bool $coroutineResult
         */
        $coroutineResult = run($this->staticPageProcessor->process(...));

        if (!$coroutineResult) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
