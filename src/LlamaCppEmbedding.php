<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlamaCppEmbedding
{
    /**
     * @param array<float> $embedding
     */
    public function __construct(
        public array $embedding,
    ) {}
}
