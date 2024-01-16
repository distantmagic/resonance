<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\OllamaClient;
use Distantmagic\Resonance\OllamaEmbeddingRequest;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'ollama:embedding',
    description: 'Generate LLM embedding'
)]
final class OllamaEmbedding extends CoroutineCommand
{
    public function __construct(
        private JsonSerializer $jsonSerializer,
        private OllamaClient $ollamaClient,
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

        $embeddingRequest = new OllamaEmbeddingRequest(
            model: $model,
            prompt: $prompt,
        );

        $embeddingResponse = $this
            ->ollamaClient
            ->generateEmbedding($embeddingRequest)
        ;

        $output->writeln(
            $this
                ->jsonSerializer
                ->serialize($embeddingResponse)
        );

        return Command::SUCCESS;
    }
}
