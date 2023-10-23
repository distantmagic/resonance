<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\StaticPageConfiguration;
use Distantmagic\Resonance\StaticPageProcessor;
use Ds\Map;
use Ds\Set;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

use function Swoole\Coroutine\run;

#[ConsoleCommand(
    name: 'generate:static-pages',
    description: 'Generate static pages'
)]
final class GenerateStaticSite extends Command
{
    public function __construct(
        private StaticPageConfiguration $staticPageConfiguration,
        private StaticPageProcessor $staticPageProcessor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var Map<SplFileInfo,Set<string>> $errors
         */
        $errors = new Map();

        /**
         * @var bool
         */
        $coroutineResult = run(function () use ($errors) {
            $errors->putAll($this->staticPageProcessor->process(
                esbuildMetafile: DM_ROOT.'/'.$this->staticPageConfiguration->esbuildMetafile,
                staticPagesInputDirectory: DM_ROOT.'/'.$this->staticPageConfiguration->inputDirectory,
                staticPagesOutputDirectory: DM_ROOT.'/'.$this->staticPageConfiguration->outputDirectory,
                staticPagesSitemap: DM_ROOT.'/'.$this->staticPageConfiguration->sitemap,
                stripOutputPrefix: $this->staticPageConfiguration->outputDirectory.'/',
            ));
        });

        if (!$coroutineResult) {
            return Command::FAILURE;
        }

        if (!$errors->isEmpty()) {
            foreach ($errors as $file => $messages) {
                foreach ($messages as $message) {
                    $output->writeln(sprintf(
                        '%s: %s',
                        $file->getRelativePathname(),
                        $message,
                    ));
                }
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
