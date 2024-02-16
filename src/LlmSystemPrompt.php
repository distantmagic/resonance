<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

abstract readonly class LlmSystemPrompt implements JsonSerializable
{
    abstract public function getPromptContent(): string;

    public function jsonSerialize(): array
    {
        return [
            'prompt' => $this->getPromptContent(),
        ];
    }
}
