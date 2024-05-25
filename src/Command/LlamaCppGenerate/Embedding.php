<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command\LlamaCppGenerate;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Command\LlamaCppGenerate;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\LlamaCppClientInterface;
use Distantmagic\Resonance\LlamaCppEmbeddingRequest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'llamacpp:embedding',
    description: 'Generate embedding based on a prompt'
)]
final class Embedding extends LlamaCppGenerate
{
    public function __construct(
        CoroutineDriverInterface $coroutineDriver,
        private readonly JsonSerializer $jsonSerializer,
        LlamaCppClientInterface $llamaCppClient,
    ) {
        parent::__construct($coroutineDriver, $llamaCppClient);
    }

    protected function executeLlamaCppCommand(InputInterface $input, OutputInterface $output, string $prompt): int
    {
        $request = new LlamaCppEmbeddingRequest($prompt);

        $embedding = $this->llamaCppClient->generateEmbedding($request);

        $output->writeln($this->jsonSerializer->serialize($embedding->embedding));

        return Command::SUCCESS;
    }
}
