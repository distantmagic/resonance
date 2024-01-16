<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OllamaEmbeddingResponse implements JsonSerializable
{
    /**
     * @param array<float> $embedding
     */
    public function __construct(public array $embedding) {}

    public function jsonSerialize(): array
    {
        return $this->embedding;
    }
}
