<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlamaCppConfiguration
{
    public function __construct(
        public ?string $apiKey,
        public float $completionTokenTimeout,
        public string $host,
        public int $port,
        public string $scheme,
    ) {}
}
