<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlamaCppExtractSubjectResult
{
    public function __construct(
        public ?string $content,
        public bool $isFailed,
    ) {}
}
