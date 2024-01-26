<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class RedisConnectionPoolConfiguration
{
    /**
     * @psalm-taint-source system_secret $dbIndex
     * @psalm-taint-source system_secret $host
     * @psalm-taint-source system_secret $password
     * @psalm-taint-source system_secret $poolPrefill
     * @psalm-taint-source system_secret $poolSize
     * @psalm-taint-source system_secret $port
     * @psalm-taint-source system_secret $prefix
     * @psalm-taint-source system_secret $timeout
     *
     * @param non-empty-string $host
     * @param non-empty-string $prefix
     */
    public function __construct(
        #[SensitiveParameter]
        public int $dbIndex,
        #[SensitiveParameter]
        public string $host,
        #[SensitiveParameter]
        public string $password,
        #[SensitiveParameter]
        public bool $poolPrefill,
        #[SensitiveParameter]
        public int $poolSize,
        #[SensitiveParameter]
        public int $port,
        #[SensitiveParameter]
        public string $prefix,
        #[SensitiveParameter]
        public int $timeout,
    ) {}
}
