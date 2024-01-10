<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OpenAPISchemaComponentsSecuritySchemesSession implements JsonSerializable
{
    public function __construct(
        private SessionConfiguration $sessionConfiguration,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'type' => 'apiKey',
            'in' => 'cookie',
            'name' => $this->sessionConfiguration->cookieName,
        ];
    }
}
