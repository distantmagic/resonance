<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface LlamaCppExtractWhenInterface
{
    public function extract(
        string $input,
        string $condition,
        LlmPersonaInterface $persona,
    ): LlamaCppExtractWhenResult;
}
