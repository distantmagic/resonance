<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements OpenAPIRouteParameterExtractorInterface<TAttribute>
 */
abstract readonly class OpenAPIRouteParameterExtractor implements OpenAPIRouteParameterExtractorInterface {}
