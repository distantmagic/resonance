<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlamaCppExtractYesNoMaybeResult
{
    public function __construct(
        public ?YesNoMaybe $result,
        public bool $isFailed,
    ) {}
}
