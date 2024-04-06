<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface LlamaCppExtractSubjectInterface
{
    public function extract(string $input, string $topic): LlamaCppExtractSubjectResult;
}
