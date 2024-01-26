<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OpenAPIConfiguration
{
    /**
     * @param non-empty-string $description
     * @param non-empty-string $title
     * @param non-empty-string $version
     */
    public function __construct(
        public string $description,
        public string $title,
        public string $version,
    ) {}
}
