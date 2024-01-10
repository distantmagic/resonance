<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use JsonSerializable;

#[Singleton]
readonly class OpenAPISchemaComponents implements JsonSerializable
{
    public function __construct(
        private OpenAPISchemaComponentsSecuritySchemes $openAPISchemaSecuritySchemes,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'securitySchemes' => $this->openAPISchemaSecuritySchemes,
        ];
    }
}
