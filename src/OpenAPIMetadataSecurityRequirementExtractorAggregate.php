<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use ReflectionClass;

/**
 * @template-implements OpenAPIMetadataSecurityRequirementExtractorInterface<Attribute>
 */
readonly class OpenAPIMetadataSecurityRequirementExtractorAggregate implements OpenAPIMetadataSecurityRequirementExtractorInterface
{
    /**
     * @var Map<class-string<Attribute>,OpenAPIMetadataSecurityRequirementExtractorInterface>
     */
    public Map $extractors;

    public function __construct()
    {
        $this->extractors = new Map();
    }

    public function extractFromHttpControllerMetadata(
        ReflectionClass $reflectionClass,
        Attribute $attribute,
    ): array {
        $extractor = $this->extractors->get($attribute::class, null);

        if ($extractor) {
            return $extractor->extractFromHttpControllerMetadata($reflectionClass, $attribute);
        }

        return [];
    }
}
