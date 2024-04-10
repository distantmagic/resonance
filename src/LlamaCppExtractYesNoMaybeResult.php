<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 */
readonly class LlamaCppExtractYesNoMaybeResult
{
    public function __construct(
        public string $input,
        public bool $isFailed,
        public ?YesNoMaybe $result,
    ) {}
}
