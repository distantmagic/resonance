<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\LlmPromptTemplate;

readonly class HermesChat extends LlmPromptTemplate
{
    public function __construct(private string $prompt) {}

    public function getPromptTemplateContent(): string
    {
        return sprintf(
            '<|im_start|%s<|im_end|>',
            $this->prompt,
        );
    }

    public function getStopWords(): array
    {
        return ['<|im_start|>', '<|im_end|>'];
    }
}
