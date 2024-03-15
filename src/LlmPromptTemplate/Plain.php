<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\LlmPromptTemplate;

readonly class Plain extends LlmPromptTemplate
{
    public function __construct(private string $prompt) {}

    public function getPromptTemplateContent(): string
    {
        return $this->prompt;
    }

    public function getStopWords(): array
    {
        return [];
    }
}
