<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\OllamaClient;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class OllamaGenerate extends CoroutineCommand
{
    abstract protected function executeOllamaCommand(InputInterface $input, OutputInterface $output, string $model, string $prompt): int;

    public function __construct(
        protected OllamaClient $ollamaClient,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function configure(): void
    {
        $this->addArgument('prompt', InputArgument::REQUIRED);
        $this->addOption(
            default: 'mistral',
            mode: InputOption::VALUE_REQUIRED,
            name: 'model',
        );
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string $model
         */
        $model = $input->getOption('model');

        /**
         * @var string $prompt
         */
        $prompt = $input->getArgument('prompt');

        return $this->executeOllamaCommand($input, $output, $model, $prompt);
    }
}
