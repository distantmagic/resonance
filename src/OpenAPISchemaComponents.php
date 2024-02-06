<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use stdClass;

/**
 * @template-implements OpenAPISerializableFieldInterface<array{
 *     schemas: object|array<non-empty-string,JsonSchemableInterface>,
 *     securitySchemes: OpenAPISchemaComponentsSecuritySchemes,
 * }>
 */
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

    /**
     * @return array<non-empty-string,JsonSchemableInterface>|object
     */
    private function serializeSchemaCollection(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array|object
    {
        /**
         * @var array<non-empty-string,JsonSchemableInterface> $schemas
         */
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
