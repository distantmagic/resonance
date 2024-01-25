<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlamaCppEmbedding
{
    /**
     * @param list<float> $embedding
     */
    public function __construct(
        public array $embedding,
    ) {}
}
