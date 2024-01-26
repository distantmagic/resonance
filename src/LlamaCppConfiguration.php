<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class LlamaCppConfiguration
{
    /**
     * @psalm-taint-source system_secret $apiKey
     * @psalm-taint-source system_secret $completionTokenTimeout
     * @psalm-taint-source system_secret $host
     * @psalm-taint-source system_secret $port
     * @psalm-taint-source system_secret $scheme
     *
     * @param null|non-empty-string $apiKey
     * @param non-empty-string      $host
     * @param non-empty-string      $scheme
     */
    public function __construct(
        #[SensitiveParameter]
        public ?string $apiKey,
        #[SensitiveParameter]
        public float $completionTokenTimeout,
        #[SensitiveParameter]
        public string $host,
        #[SensitiveParameter]
        public int $port,
        #[SensitiveParameter]
        public string $scheme,
    ) {}
}
