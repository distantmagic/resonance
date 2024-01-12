<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OpenAPISchemaComponents implements OpenAPISerializableFieldInterface
{
    public function __construct(
        private OpenAPISchemaComponentsSecuritySchemes $openAPISchemaSecuritySchemes,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        return [
            'schemas' => $this->serializeSchemaCollection($openAPIReusableSchemaCollection),
            'securitySchemes' => $this->openAPISchemaSecuritySchemes,
        ];
    }

    private function serializeSchemaCollection(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        $schemas = [];

        foreach ($openAPIReusableSchemaCollection->references as $jsonSchema => $referenceId) {
            $schemas[$referenceId] = $jsonSchema;
        }

        return $schemas;
    }
}
