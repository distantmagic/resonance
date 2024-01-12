<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OpenAPISchemaResponse implements OpenAPISerializableFieldInterface
{
    public function __construct(
        public ContentType $contentType,
        public JsonSchema $jsonSchema,
        public string $jsonSchemaName,
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
                'schema' => $openAPIReusableSchemaCollection->reuse($this->jsonSchema, $this->jsonSchemaName),
            ],
        ];

        return $response;
    }
}
