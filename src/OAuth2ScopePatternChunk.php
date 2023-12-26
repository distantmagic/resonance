<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OAuth2ScopePatternChunk
{
    public string $basename;

    public function __construct(public string $chunk)
    {
        $this->basename = trim($chunk, '{}');
    }

    public function isVariable(): bool
    {
        return str_starts_with($this->chunk, '{') && str_ends_with($this->chunk, '}');
    }
}
