<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class LlmChatHistoryRenderer
{
    public function __construct(
        private LlmChatMessageRendererInterface $llmChatMessageRenderer,
    ) {}

    public function renderLlmChatHistory(
        LlmChatHistory $llmChatHistory,
    ): string {
        $ret = '';

        foreach ($llmChatHistory->messages as $message) {
            $ret .= $this->llmChatMessageRenderer->renderLlmChatMessage($message)."\n";
        }

        return $ret;
    }
}
