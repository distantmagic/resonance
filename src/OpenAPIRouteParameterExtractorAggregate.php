<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

/**
 * @template-implements OpenAPIRouteParameterExtractorInterface<Attribute>
 */
readonly class OpenAPIRouteParameterExtractorAggregate implements OpenAPIRouteParameterExtractorInterface
{
    /**
     * @var Map<class-string<Attribute>,OpenAPIRouteParameterExtractorInterface>
     */
    public Map $extractors;

    public function __construct()
    {
        $this->extractors = new Map();
    }

    public function extractFromHttpControllerParameter(
        Attribute $attribute,
        string $parameterClass,
        string $parameterName,
    ): array {
        $extractor = $this->extractors->get($attribute::class, null);

        if ($extractor) {
            return $extractor->extractFromHttpControllerParameter(
                $attribute,
                $parameterClass,
                $parameterName,
            );
        }

        return [];
    }
}
