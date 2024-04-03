<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;

interface LlamaCppClientInterface
{
    public function generateCompletion(LlamaCppCompletionRequest $request): LlamaCppCompletionIterator;

    public function generateEmbedding(LlamaCppEmbeddingRequest $request): LlamaCppEmbedding;

    /**
     * @return Generator<LlamaCppInfill>
     */
    public function generateInfill(LlamaCppInfillRequest $request): Generator;

    public function getHealth(): LlamaCppHealthStatus;
}
