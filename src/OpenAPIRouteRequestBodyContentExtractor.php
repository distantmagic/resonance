<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements OpenAPIRouteRequestBodyContentExtractorInterface<TAttribute>
 */
abstract readonly class OpenAPIRouteRequestBodyContentExtractor implements OpenAPIRouteRequestBodyContentExtractorInterface {}
