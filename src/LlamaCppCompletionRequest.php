<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class LlamaCppCompletionRequest implements JsonSerializable
{
    public function __construct(
        public LlmPromptTemplate $promptTemplate,
        public ?BackusNaurFormGrammar $backusNaurFormGrammar = null,
        public ?LlmPrompt $llmSystemPrompt = null,
    ) {}

    public function jsonSerialize(): array
    {
        $parameters = [
            'cache_prompt' => true,
            // 'n_predict' => 200,
            'prompt' => $this->promptTemplate->getPromptTemplateContent(),
            'stop' => $this->promptTemplate->getStopWords(),
            'stream' => true,
        ];

        if ($this->backusNaurFormGrammar) {
            $parameters['grammar'] = $this->backusNaurFormGrammar->getGrammarContent();
        }

        if ($this->llmSystemPrompt) {
            $parameters['system_prompt'] = [
                'prompt' => $this->llmSystemPrompt->getPromptContent(),
            ];
        }

        return $parameters;
    }
}
