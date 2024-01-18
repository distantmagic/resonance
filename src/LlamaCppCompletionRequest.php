<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class LlamaCppCompletionRequest implements JsonSerializable
{
    public function __construct(
        public string $prompt,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'prompt' => sprintf('[INT]%s[SYST]', $this->prompt),
            'stop' => [
                '[INST]',
                '[SYST]',
            ],
            'stream' => true,
        ];
    }
}
