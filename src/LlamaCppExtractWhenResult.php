<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 */
readonly class LlamaCppExtractWhenResult
{
    public function __construct(
        public string $condition,
        public string $input,
        public bool $isFailed,
        public bool $isMatched,
        public YesNoMaybe $result,
    ) {}
}
