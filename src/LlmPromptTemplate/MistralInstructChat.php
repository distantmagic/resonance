<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\LlmPromptTemplate;

readonly class MistralInstructChat extends LlmPromptTemplate
{
    public function __construct(private string $prompt) {}

    public function getPromptTemplateContent(): string
    {
        return sprintf(
            '[INST]%s[/INST]',
            $this->prompt,
        );
    }

    public function getStopWords(): array
    {
        return ['[INST]', '[/INST]'];
    }
}
