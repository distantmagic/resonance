<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class OllamaConfiguration
{
    public function __construct(
        public string $host,
        public int $port,
        public string $scheme,
    ) {}
}
