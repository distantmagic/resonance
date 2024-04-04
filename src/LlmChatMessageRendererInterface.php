<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface LlmChatMessageRendererInterface
{
    public function renderLlmChatMessage(LlmChatMessage $llmChatMessage): string;
}
