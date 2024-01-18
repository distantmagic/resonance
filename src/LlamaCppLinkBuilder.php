<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class LlamaCppLinkBuilder
{
    public function __construct(
        private LlamaCppConfiguration $ollamaConfiguration,
    ) {}

    public function build(string $path): string
    {
        return sprintf(
            '%s://%s:%d%s',
            $this->ollamaConfiguration->scheme,
            $this->ollamaConfiguration->host,
            $this->ollamaConfiguration->port,
            $path,
        );
    }
}
