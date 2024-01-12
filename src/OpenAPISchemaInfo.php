<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

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
