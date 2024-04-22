<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;

interface LlamaCppExtractWhenInterface
{
    public function extract(
        string $input,
        string $condition,
        LlmPersonaInterface $persona,
    ): LlamaCppExtractWhenResult;

    /**
     * @return Generator<mixed,LlmCompletionProgressInterface,mixed,LlamaCppExtractWhenResult>
     */
    public function extractWithProgress(
        string $input,
        string $condition,
        LlmPersonaInterface $persona,
    ): Generator;
}
