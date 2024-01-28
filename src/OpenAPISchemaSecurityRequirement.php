<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template-implements OpenAPISerializableFieldInterface<
 *     array<
 *         non-empty-string,
 *         array<non-empty-string>
 *     >
 * >
 */
readonly class OpenAPISchemaSecurityRequirement implements OpenAPISerializableFieldInterface
{
    /**
     * @param array<non-empty-string> $values
     */
    public function __construct(
        public OpenAPISecuritySchema $openAPISecuritySchema,
        public array $values = [],
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        return [
            $this->openAPISecuritySchema->name => $this->values,
        ];
    }
}
