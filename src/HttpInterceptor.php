<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\StreamInterface;
use Stringable;

/**
 * @template TClass
 *
 * @template-implements HttpInterceptorInterface<TClass>
 */
abstract readonly class HttpInterceptor implements HttpInterceptorInterface
{
    public function createStream(string|Stringable $contents): StreamInterface
    {
        return new PsrStringStream($contents);
    }
}
