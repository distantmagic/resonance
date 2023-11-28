<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class SessionConfiguration
{
    public function __construct(
        public string $cookieName,
        public int $cookieLifespan,
        public string $cookieSameSite,
        public string $redisConnectionPool,
    ) {}
}
