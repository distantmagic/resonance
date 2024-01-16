<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OllamaRequestStopDelimiter implements JsonSerializable
{
    public function __construct(
        public string $instructions = '[INST]',
        public string $system = '[SYS]',
    ) {}

    public function jsonSerialize(): array
    {
        return [
            $this->instructions,
            $this->system,
        ];
    }
}
