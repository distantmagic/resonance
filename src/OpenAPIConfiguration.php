<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OpenAPIConfiguration
{
    public function __construct(
        public string $description,
        public string $title,
    ) {}
}
