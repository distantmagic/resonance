<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Redis;

readonly class RedisConnection
{
    public Redis $redis;

    public function __construct(
        RedisConfiguration $redisConfiguration,
        private RedisConnectionPoolRepository $redisConnectionPoolRepository,
        string $redisPrefix,
        private string $connectionPoolName = 'default',
    ) {
        $redisPrefix = $redisConfiguration
            ->connectionPoolConfiguration
            ->get($connectionPoolName)
            ->prefix
        ;

        $this->redis = $this
            ->redisConnectionPoolRepository
            ->getConnection($connectionPoolName)
        ;
        $this->redis->setOption(Redis::OPT_PREFIX, $redisPrefix);
    }

    public function __destruct()
    {
        $this->redisConnectionPoolRepository->putConnection(
            $this->connectionPoolName,
            $this->redis,
        );
    }
}
