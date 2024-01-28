<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-type PArraySerializedOpenAPISchemaResponse = array{
 *     description?: non-empty-string,
 *     content: array<string, array{ schema: JsonSchema }>
 * }
 *
 * @template-implements OpenAPISerializableFieldInterface<PArraySerializedOpenAPISchemaResponse>
 */
readonly class OpenAPISchemaResponse implements OpenAPISerializableFieldInterface
{
    /**
     * @param null|non-empty-string $description
     */
    public function __construct(
        public ContentType $contentType,
        public JsonSchema $jsonSchema,
        public int $status,
        public ?string $description = null,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        $response = [];

        if (isset($this->description)) {
            $response['description'] = $this->description;
        }

        $response['content'] = [
            $this->contentType->value => [
                'schema' => $openAPIReusableSchemaCollection->reuse($this->jsonSchema),
            ],
        ];

        return $response;
    }
}
