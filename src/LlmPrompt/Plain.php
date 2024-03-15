<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPrompt;

use Distantmagic\Resonance\LlmPrompt;

readonly class Plain extends LlmPrompt
{
    /**
     * @param non-empty-string $prompt
     */
    public function __construct(
        private string $prompt
    ) {}

    public function getPromptContent(): string
    {
        return $this->prompt;
    }
}
