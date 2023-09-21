<?php

declare(strict_types=1);

namespace Resonance;

readonly class HttpControllerParameterResolutionResult
{
    public function __construct(
        public HttpControllerParameterResolutionStatus $status,
        public mixed $value = null,
    ) {}
}
