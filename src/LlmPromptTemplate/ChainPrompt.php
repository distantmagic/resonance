<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlmPromptTemplate;

#[Singleton]
readonly class ChainPrompt extends LlmPromptTemplate
{
    private string $prompt;

    /**
     * @param array<LlmPromptTemplate> $prompts
     */
    public function __construct(array $prompts)
    {
        $gluedPrompt = '';

        foreach ($prompts as $prompt) {
            $gluedPrompt .= $prompt->getPromptTemplateContent();
        }

        $this->prompt = $gluedPrompt;
    }

    public function getPromptTemplateContent(): string
    {
        return $this->prompt;
    }
}
