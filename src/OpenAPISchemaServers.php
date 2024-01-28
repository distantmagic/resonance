<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template-implements OpenAPISerializableFieldInterface<list<array{
 *     url: non-empty-string,
 * }>>
 */
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
