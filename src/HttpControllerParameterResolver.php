<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TAttribute of Attribute
 *
 * @template-implements HttpControllerParameterResolverInterface<TAttribute>
 */
abstract readonly class HttpControllerParameterResolver implements HttpControllerParameterResolverInterface {}
