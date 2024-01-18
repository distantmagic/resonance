<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

readonly class LlamaCppInfill implements Stringable
{
    public function __construct(
        public string $after,
        public string $before,
        public string $content,
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }
}
