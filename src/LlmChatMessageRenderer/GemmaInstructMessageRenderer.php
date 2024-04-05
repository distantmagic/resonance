<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmChatMessageRenderer;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlmChatMessage;
use Distantmagic\Resonance\LlmChatMessageRenderer;

#[Singleton]
readonly class GemmaInstructMessageRenderer extends LlmChatMessageRenderer
{
    public function renderLlmChatMessage(LlmChatMessage $llmChatMessage): string
    {
        return <<<FORMATTED
        <start_of_turn>{$llmChatMessage->actor}
        {$llmChatMessage->message}<end_of_turn>
        FORMATTED;
    }
}
