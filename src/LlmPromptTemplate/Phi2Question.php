<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\LlmPromptTemplate;

readonly class Phi2Question extends LlmPromptTemplate
{
    public function __construct(private string $prompt) {}

    public function getPromptTemplateContent(): string
    {
        return sprintf(
            "Question: %s\nAnswer: ",
            $this->prompt,
        );
    }

    public function getStopWords(): array
    {
        return ['Question:', 'Answer:'];
    }
}
