<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class DatabaseConnectionPoolConfiguration
{
    public function __construct(
        #[SensitiveParameter]
        public string $database,
        #[SensitiveParameter]
        public DatabaseConnectionPoolDriverName $driver,
        #[SensitiveParameter]
        public ?string $host,
        #[SensitiveParameter]
        public bool $logQueries,
        #[SensitiveParameter]
        public string $password,
        #[SensitiveParameter]
        public bool $poolPrefill,
        #[SensitiveParameter]
        public int $poolSize,
        #[SensitiveParameter]
        public int $port,
        #[SensitiveParameter]
        public ?string $unixSocket,
        #[SensitiveParameter]
        public string $username,
    ) {}
}
