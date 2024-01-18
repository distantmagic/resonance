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

            // prompt field should not be mandatory, its a bug:
            // https://github.com/ggerganov/llama.cpp/issues/4027
            'prompt' => 'prompt',
        ];
    }
}
