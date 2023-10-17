<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class HttpMiddlewareAttribute
{
    public function __construct(
        public readonly HttpMiddlewareInterface $httpMiddleware,
        public readonly Attribute $attribute,
    ) {}
}
