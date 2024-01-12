<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\HttpResponder\HttpController;
use ReflectionClass;

/**
 * @template TAttribute of Attribute
 */
interface OpenAPIMetadataResponseExtractorInterface
{
    /**
     * @param ReflectionClass<HttpController> $reflectionClass
     * @param TAttribute                      $attribute
     *
     * @return array<OpenAPISchemaResponse>
     */
    public function extractFromHttpControllerMetadata(
        ReflectionClass $reflectionClass,
        Attribute $attribute,
    ): array;
}
