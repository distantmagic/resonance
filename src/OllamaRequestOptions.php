<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OllamaRequestOptions implements JsonSerializable
{
    public function __construct(
        public float $numPredict = -1,
        public float $temperature = 0.5,
        public OllamaRequestStopDelimiter $stopDelimiter = new OllamaRequestStopDelimiter(),
    ) {}

    public function jsonSerialize(): array
    {
        $ret = [];

        $ret['num_predict'] = $this->numPredict;
        $ret['stop'] = $this->stopDelimiter;
        $ret['temperature'] = $this->temperature;

        return $ret;
    }
}
