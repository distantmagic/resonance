<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements HttpMiddlewareInterface<TAttribute>
 */
abstract readonly class HttpMiddleware implements HttpMiddlewareInterface {}
