<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OpenAPISchemaInfo implements JsonSerializable
{
    public function __construct(
        private OpenAPIConfiguration $openAPIConfiguration,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'description' => $this->openAPIConfiguration->description,
            'title' => $this->openAPIConfiguration->title,
            'version' => $this->openAPIConfiguration->version,
        ];
    }
}
