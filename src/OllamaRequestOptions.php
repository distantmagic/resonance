<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OllamaRequestOptions implements JsonSerializable
{
    public function __construct(
        public float $temperature = 0.8,
        public OllamaRequestStopDelimiter $stopDelimiter = new OllamaRequestStopDelimiter(),
    ) {}

    public function jsonSerialize(): array
    {
        $ret = [];

        if (isset($this->stopDelimiter)) {
            $ret['stop'] = $this->stopDelimiter;
        }

        $ret['temperature'] = $this->temperature;

        return $ret;
    }
}
