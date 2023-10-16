<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class RedisConnectionPoolConfiguration
{
    public function __construct(
        #[SensitiveParameter]
        public int $dbIndex,
        #[SensitiveParameter]
        public string $host,
        #[SensitiveParameter]
        public string $password,
        #[SensitiveParameter]
        public int $port,
        #[SensitiveParameter]
        public string $prefix,
        #[SensitiveParameter]
        public int $timeout,
    ) {}
}
