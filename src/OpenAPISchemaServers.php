<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OpenAPISchemaServers implements OpenAPISerializableFieldInterface
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        return [
            [
                'url' => $this->applicationConfiguration->url,
            ],
        ];
    }
}
