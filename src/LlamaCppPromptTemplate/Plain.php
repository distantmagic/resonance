<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlamaCppPromptTemplate;

use Distantmagic\Resonance\LlamaCppPromptTemplate;

readonly class Plain extends LlamaCppPromptTemplate
{
    public function __construct(private string $prompt) {}

    public function __toString(): string
    {
        return $this->prompt;
    }
}
