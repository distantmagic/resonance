<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class HttpPreprocessorAttribute
{
    public function __construct(
        public readonly HttpPreprocessorInterface $httpPreprocessor,
        public readonly Attribute $attribute,
    ) {}
}
