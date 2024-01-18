<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command\OllamaGenerate;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Command\OllamaGenerate;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\OllamaClient;
use Distantmagic\Resonance\OllamaEmbeddingRequest;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'ollama:embedding',
    description: 'Generate LLM embedding'
)]
final class Embedding extends OllamaGenerate
{
    public function __construct(
        private JsonSerializer $jsonSerializer,
        OllamaClient $ollamaClient,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($ollamaClient, $swooleConfiguration);
    }

    protected function executeOllamaCommand(InputInterface $input, OutputInterface $output, string $model, string $prompt): int
    {
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
