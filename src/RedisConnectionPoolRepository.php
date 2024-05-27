<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use OutOfBoundsException;
use Redis;
use Swoole\Database\RedisPool;

readonly class RedisConnectionPoolRepository implements RedisConnectionPoolRepositoryInterface
{
    /**
     * @var Map<string,RedisPool>
     */
    public Map $redisConnectionPool;

    public function __construct()
    {
        $this->redisConnectionPool = new Map();
    }

    public function getConnection(string $name): Redis
    {
        if (!$this->redisConnectionPool->hasKey($name)) {
            throw new OutOfBoundsException(sprintf(
                'Redis connection pool is not configured: "%s". Available connection pools: "%s"',
                $name,
                $this->redisConnectionPool->keys()->join('", "'),
            ));
        }

        /**
         * @var Redis
         */
        return $this->redisConnectionPool->get($name)->get();
    }

    public function putConnection(string $name, Redis $redis): void
    {
        $this->redisConnectionPool->get($name)->put($redis);
    }
}
