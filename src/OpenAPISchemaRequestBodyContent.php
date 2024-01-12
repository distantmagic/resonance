<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OpenAPISchemaRequestBodyContent implements OpenAPISerializableFieldInterface
{
    public function __construct(
        public string $jsonSchemaName,
        public string $mimeType,
        public JsonSchema $jsonSchema,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        return [
            'schema' => $openAPIReusableSchemaCollection->reuse($this->jsonSchema, $this->jsonSchemaName),
        ];
    }
}
