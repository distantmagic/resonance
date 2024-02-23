<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\LlmPromptTemplate;

readonly class GemmaInstructChat extends LlmPromptTemplate
{
    public function __construct(
        private string $actor,
        private string $prompt,
    ) {}

    public function getPromptTemplateContent(): string
    {
        return sprintf(
            "<start_of_turn>%s\n%s<end_of_turn>",
            $this->actor,
            $this->prompt,
        );
    }
}
