<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-type PArraySerializedOpenAPISchemaRequestBodyContent = array{
 *     schema: JsonSchema
 * }
 *
 * @template-implements OpenAPISerializableFieldInterface<PArraySerializedOpenAPISchemaRequestBodyContent>
 */
readonly class OpenAPISchemaRequestBodyContent implements OpenAPISerializableFieldInterface
{
    /**
     * @param non-empty-string $mimeType
     */
    public function __construct(
        public string $mimeType,
        public JsonSchema $jsonSchema,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        return [
            'schema' => $openAPIReusableSchemaCollection->reuse($this->jsonSchema),
        ];
    }
}
