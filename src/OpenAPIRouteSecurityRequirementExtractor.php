<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements OpenAPIRouteSecurityRequirementExtractorInterface<TAttribute>
 */
abstract readonly class OpenAPIRouteSecurityRequirementExtractor implements OpenAPIRouteSecurityRequirementExtractorInterface {}
