<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OllamaChatRequest implements JsonSerializable
{
    /**
     * @param array<OllamaChatMessage> $messages
     */
    public function __construct(
        public string $model,
        public array $messages,
        public OllamaRequestOptions $options = new OllamaRequestOptions(),
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'model' => $this->model,
            'messages' => $this->messages,
            'options' => $this->options,
            'raw' => true,
            'stream' => true,
        ];
    }
}
