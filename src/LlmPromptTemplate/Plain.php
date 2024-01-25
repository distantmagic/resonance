<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\LlmPromptTemplate;

readonly class Plain extends LlmPromptTemplate
{
    /**
     * @param non-empty-string $prompt
     */
    public function __construct(private string $prompt) {}

    public function __toString(): string
    {
        return $this->prompt;
    }
}
