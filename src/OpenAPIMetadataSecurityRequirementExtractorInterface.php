<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\HttpResponder\HttpController;
use ReflectionClass;

/**
 * @template TAttribute of Attribute
 */
interface OpenAPIMetadataSecurityRequirementExtractorInterface
{
    /**
     * @param ReflectionClass<HttpController> $reflectionClass
     * @param TAttribute                      $attribute
     *
     * @return list<OpenAPISchemaSecurityRequirement>
     */
    public function extractFromHttpControllerMetadata(
        ReflectionClass $reflectionClass,
        Attribute $attribute,
    ): array;
}
