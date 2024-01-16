<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OllamaCompletionRequest implements JsonSerializable
{
    public function __construct(
        public string $model,
        public string $prompt,
        public OllamaRequestOptions $options = new OllamaRequestOptions(),
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'model' => $this->model,
            'options' => $this->options,
            'prompt' => sprintf(
                '%s%s%s',
                $this->options->stopDelimiter->instructions,
                $this->prompt,
                $this->options->stopDelimiter->system,
            ),
            'raw' => true,
            'stream' => true,
        ];
    }
}
