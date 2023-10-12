<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class DatabaseConfiguration
{
    public function __construct(
        #[SensitiveParameter]
        public string $database,
        #[SensitiveParameter]
        public string $host,
        #[SensitiveParameter]
        public bool $logQueries,
        #[SensitiveParameter]
        public string $password,
        #[SensitiveParameter]
        public int $port,
        #[SensitiveParameter]
        public string $username,
    ) {}
}
