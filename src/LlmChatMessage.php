<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlmChatMessage
{
    public function __construct(
        public string $actor,
        public string $message,
    ) {}
}
