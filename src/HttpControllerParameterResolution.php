<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class HttpControllerParameterResolution
{
    public function __construct(
        public HttpControllerParameterResolutionStatus $status,
        public mixed $value = null,
    ) {}
}
