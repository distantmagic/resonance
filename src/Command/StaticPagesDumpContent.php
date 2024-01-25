<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\StaticPageAggregate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'static-pages:dump-content',
    description: 'Dumps static pages content into JSONL'
)]
final class StaticPagesDumpContent extends Command
{
    public function __construct(
        private StaticPageAggregate $staticPageAggregate,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->staticPageAggregate->staticPages as $staticPage) {
            $output->writeln(json_encode([
                'basename' => $staticPage->getBasename(),
                'content_raw' => $staticPage->content,
            ]));
        }

        return Command::SUCCESS;
    }
}
