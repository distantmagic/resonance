<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class LlamaCppInfillRequest implements JsonSerializable
{
    public function __construct(
        public string $after,
        public string $before,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'infill_prefix' => $this->before,
            'infill_suffix' => $this->after,
        ];
    }
}
