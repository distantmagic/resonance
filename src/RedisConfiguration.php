<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class RedisConfiguration
{
    public function __construct(
        #[SensitiveParameter]
        public string $host,
        #[SensitiveParameter]
        public string $password,
        #[SensitiveParameter]
        public int $port,
        #[SensitiveParameter]
        public string $prefix,
    ) {}
}
