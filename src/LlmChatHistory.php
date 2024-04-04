<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlmChatHistory
{
    /**
     * @param array<LlmChatMessage> $messages
     */
    public function __construct(
        public array $messages,
    ) {}
}
