<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 */
readonly class LlamaCppCompletionToken implements Stringable
{
    public function __construct(
        public string $content,
        public bool $isFailed,
        public bool $isLastToken,
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }
}
