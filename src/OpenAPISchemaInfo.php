<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template-implements OpenAPISerializableFieldInterface<array{
 *     description: non-empty-string,
 *     title: non-empty-string,
 *     version: non-empty-string,
 * }>
 */
readonly class OpenAPISchemaInfo implements OpenAPISerializableFieldInterface
{
    public function __construct(
        private OpenAPIConfiguration $openAPIConfiguration,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        return [
            'description' => $this->openAPIConfiguration->description,
            'title' => $this->openAPIConfiguration->title,
            'version' => $this->openAPIConfiguration->version,
        ];
    }
}
