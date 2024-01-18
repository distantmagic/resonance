<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

readonly class LlamaCppCompletionToken implements Stringable
{
    public function __construct(
        public string $content,
        public bool $isLast,
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }
}
