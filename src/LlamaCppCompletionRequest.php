<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlamaCppCompletionRequest
{
    public function __construct(
        public LlmChatHistory $llmChatHistory,
        public ?BackusNaurFormGrammar $backusNaurFormGrammar = null,
    ) {}

    public function toJsonSerializable(LlmChatHistoryRenderer $llmChatHistoryRenderer): array
    {
        $parameters = [
            'cache_prompt' => true,
            'n_predict' => 1000,
            'prompt' => $llmChatHistoryRenderer->renderLlmChatHistory($this->llmChatHistory),
            'stream' => true,
        ];

        if ($this->backusNaurFormGrammar) {
            $parameters['grammar'] = $this->backusNaurFormGrammar->getGrammarContent();
        }

        return $parameters;
    }
}
