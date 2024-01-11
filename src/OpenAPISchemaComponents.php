<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use JsonSerializable;
use stdClass;

#[Singleton]
readonly class OpenAPISchemaComponents implements JsonSerializable
{
    public function __construct(
        private OpenAPISchemaComponentsSecuritySchemes $openAPISchemaSecuritySchemes,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'schemas' => new stdClass(),
            'securitySchemes' => $this->openAPISchemaSecuritySchemes,
        ];
    }
}
