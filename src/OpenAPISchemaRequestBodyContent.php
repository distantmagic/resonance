<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-type PArraySerializedOpenAPISchemaRequestBodyContent = array{
 *     schema: array
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
        public JsonSchemableInterface $jsonSchemable,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        return [
            'schema' => $openAPIReusableSchemaCollection->reuse($this->jsonSchemable),
        ];
    }
}
