<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlamaCppClientResponseChunk
{
    public function __construct(
        public ObservableTaskStatus $status,
        public string $chunk,
    ) {}
}
