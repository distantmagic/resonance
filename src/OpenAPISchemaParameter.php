<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OpenAPISchemaParameter implements JsonSerializable
{
    public function __construct(
        private OpenAPIParameterIn $in,
        private array $jsonSchema,
        private string $name,
        private bool $required,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'in' => $this->in->value,
            'name' => $this->name,
            'required' => $this->required,
            'schema' => $this->jsonSchema,
        ];
    }
}
