<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class DatabaseConnectionPoolConfiguration
{
    /**
     * @psalm-taint-source file $unixSocket
     *
     * @param non-empty-string      $host
     * @param null|string           $password
     * @param null|non-empty-string $unixSocket
     * @param non-empty-string      $username
     */
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
        public ?string $password,
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
