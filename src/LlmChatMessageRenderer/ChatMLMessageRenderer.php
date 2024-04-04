<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmChatMessageRenderer;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlmChatMessage;
use Distantmagic\Resonance\LlmChatMessageRenderer;

#[Singleton]
readonly class ChatMLMessageRenderer extends LlmChatMessageRenderer
{
    public function renderLlmChatMessage(LlmChatMessage $llmChatMessage): string
    {
        return <<<FORMATTED
        <|im_start|>{$llmChatMessage->actor}
        {$llmChatMessage->message}<|im_end|>
        FORMATTED;
    }
}
