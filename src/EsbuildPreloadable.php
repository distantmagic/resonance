<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class EsbuildPreloadable
{
    public function __construct(
        public string $pathname,
        public EsbuildPreloadableType $type,
    ) {}
}
