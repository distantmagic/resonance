<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class SwooleConfiguration
{
    public function __construct(
        public string $host,
        public int $logLevel,
        public int $port,
        public string $sslCertFile,
        public string $sslKeyFile,
    ) {}
}
