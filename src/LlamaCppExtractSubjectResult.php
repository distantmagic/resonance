<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 */
readonly class LlamaCppExtractSubjectResult
{
    public function __construct(
        public string $content,
        public string $input,
        public bool $isMatched,
        public bool $isFailed,
        public string $topic,
    ) {}
}
