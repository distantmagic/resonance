<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use ReflectionClass;

/**
 * @template-implements OpenAPIMetadataResponseExtractorInterface<Attribute>
 */
readonly class OpenAPIMetadataResponseExtractorAggregate implements OpenAPIMetadataResponseExtractorInterface
{
    /**
     * @var Map<class-string<Attribute>,OpenAPIMetadataResponseExtractorInterface>
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
