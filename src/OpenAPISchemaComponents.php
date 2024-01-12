<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use stdClass;

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

    private function serializeSchemaCollection(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array|object
    {
        $schemas = [];

        foreach ($openAPIReusableSchemaCollection->references as $jsonSchema => $referenceId) {
            $schemas[$referenceId] = $jsonSchema;
        }

        if (empty($schemas)) {
            return new stdClass();
        }

        return $schemas;
    }
}
