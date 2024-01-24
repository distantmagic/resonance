<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class LlamaCppCompletionRequest implements JsonSerializable
{
    public function __construct(
        public LlamaCppPromptTemplate $promptTemplate,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'prompt' => $this->promptTemplate,
            'stream' => true,
        ];
    }
}
