<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command\LlamaCppGenerate;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Command\LlamaCppGenerate;
use Distantmagic\Resonance\LlamaCppCompletionRequest;
use Distantmagic\Resonance\LlmChatHistory;
use Distantmagic\Resonance\LlmChatMessage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'llamacpp:completion',
    description: 'Generate completion based on a prompt'
)]
final class Completion extends LlamaCppGenerate
{
    protected function executeLlamaCppCommand(InputInterface $input, OutputInterface $output, string $prompt): int
    {
        $request = new LlamaCppCompletionRequest(
            llmChatHistory: new LlmChatHistory([
                new LlmChatMessage('user', $prompt),
            ]),
        );

        $completion = $this->llamaCppClient->generateCompletion($request);

        foreach ($completion as $token) {
            $output->write((string) $token);
        }

        return Command::SUCCESS;
    }
}
