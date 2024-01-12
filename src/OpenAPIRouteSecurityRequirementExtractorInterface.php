<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 */
interface OpenAPIRouteSecurityRequirementExtractorInterface
{
    /**
     * @param TAttribute       $attribute
     * @param class-string     $parameterClass
     * @param non-empty-string $parameterName
     *
     * @return array<OpenAPISchemaSecurityRequirement>
     */
    public function extractFromHttpControllerParameter(
        Attribute $attribute,
        string $parameterClass,
        string $parameterName,
    ): array;
}
