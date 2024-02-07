<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TValue
 */
readonly class HttpControllerParameterResolution
{
    /**
     * @param TValue $value
     */
    public function __construct(
        public HttpControllerParameterResolutionStatus $status,
        public mixed $value = null,
    ) {}
}
