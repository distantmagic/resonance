<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OpenAPISchemaServers implements JsonSerializable
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            [
                'url' => $this->applicationConfiguration->url,
            ],
        ];
    }
}
