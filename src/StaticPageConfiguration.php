<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class StaticPageConfiguration
{
    public function __construct(
        public string $baseUrl,
    ) {}
}
