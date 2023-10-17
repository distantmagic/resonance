<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TClass
 *
 * @template-implements HttpInterceptorInterface<TClass>
 */
abstract readonly class HttpInterceptor implements HttpInterceptorInterface {}
