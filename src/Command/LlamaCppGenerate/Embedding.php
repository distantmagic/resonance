<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command\LlamaCppGenerate;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Command\LlamaCppGenerate;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppEmbeddingRequest;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'llamacpp:embedding',
    description: 'Generate embedding based on a prompt'
)]
final class Embedding extends LlamaCppGenerate
{
    public function __construct(
        private JsonSerializer $jsonSerializer,
        LlamaCppClient $llamaCppClient,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($llamaCppClient, $swooleConfiguration);
    }

    protected function executeLlamaCppCommand(InputInterface $input, OutputInterface $output, string $prompt): int
    {
        $request = new LlamaCppEmbeddingRequest($prompt);

        $embedding = $this->llamaCppClient->generateEmbedding($request);

        $output->writeln($this->jsonSerializer->serialize($embedding->embedding));

        return Command::SUCCESS;
    }
}
