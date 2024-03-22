<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class PromptSubjectResponseChunk
{
    public function __construct(
        public bool $isFailed,
        public bool $isLastChunk,
        public mixed $payload,
    ) {}
}
