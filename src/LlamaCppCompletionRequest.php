<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class LlamaCppCompletionRequest implements JsonSerializable
{
    public function __construct(
        public LlmPromptTemplate $promptTemplate,
        public ?BackusNaurFormGrammar $backusNaurFormGrammar = null,
        public ?LlmSystemPrompt $llmSystemPrompt = null,
    ) {}

    public function jsonSerialize(): array
    {
        $parameters = [
            'n_predict' => 400,
            'prompt' => $this->promptTemplate,
            'stream' => true,
        ];

        if ($this->backusNaurFormGrammar) {
            $parameters['grammar'] = $this->backusNaurFormGrammar;
        }

        if ($this->llmSystemPrompt) {
            $parameters['system_prompt'] = $this->llmSystemPrompt;
        }

        return $parameters;
    }
}
