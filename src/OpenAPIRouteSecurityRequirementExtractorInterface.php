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
     * @return list<OpenAPISchemaSecurityRequirement>
     */
    public function extractFromHttpControllerParameter(
        Attribute $attribute,
        string $parameterClass,
        string $parameterName,
    ): array;
}
