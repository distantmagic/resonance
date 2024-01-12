<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements OpenAPIMetadataSecurityRequirementExtractorInterface<TAttribute>
 */
abstract readonly class OpenAPIMetadataSecurityRequirementExtractor implements OpenAPIMetadataSecurityRequirementExtractorInterface {}
