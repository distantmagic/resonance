<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Redis;

interface RedisConnectionPoolRepositoryInterface
{
    public function getConnection(string $name): Redis;

    public function putConnection(string $name, Redis $redis): void;
}
