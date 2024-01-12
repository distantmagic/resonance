<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements OpenAPIMetadataResponseExtractorInterface<TAttribute>
 */
abstract readonly class OpenAPIMetadataResponseExtractor implements OpenAPIMetadataResponseExtractorInterface {}
