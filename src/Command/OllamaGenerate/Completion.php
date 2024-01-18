<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command\OllamaGenerate;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Command\OllamaGenerate;
use Distantmagic\Resonance\OllamaCompletionRequest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'ollama:completion',
    description: 'Generate LLM completion'
)]
final class Completion extends OllamaGenerate
{
    protected function executeOllamaCommand(InputInterface $input, OutputInterface $output, string $model, string $prompt): int
    {
        $completionRequest = new OllamaCompletionRequest(
            model: $model,
            prompt: $prompt,
        );

        foreach ($this->ollamaClient->generateCompletion($completionRequest) as $token) {
            $output->write((string) $token);
        }

        return Command::SUCCESS;
    }
}
