<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements HttpPreprocessorInterface<TAttribute>
 */
abstract readonly class HttpPreprocessor implements HttpPreprocessorInterface {}
