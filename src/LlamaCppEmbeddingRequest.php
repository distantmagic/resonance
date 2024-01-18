<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class LlamaCppEmbeddingRequest implements JsonSerializable
{
    public function __construct(
        public string $content,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
