<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmChatMessageRenderer;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlmChatMessage;
use Distantmagic\Resonance\LlmChatMessageRenderer;

#[Singleton]
readonly class Phi2QuestionRenderer extends LlmChatMessageRenderer
{
    public function renderLlmChatMessage(LlmChatMessage $llmChatMessage): string
    {
        return <<<FORMATTED
        Question: $llmChatMessage->message
        Answer:
        FORMATTED;
    }
}
