<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Redis;
use Swoole\Event;

readonly class Session
{
    public Map $data;
    private Redis $redis;

    public function __construct(
        RedisConfiguration $redisConfiguration,
        private RedisConnectionPoolRepository $redisConnectionPoolRepository,
        private SessionConfiguration $sessionConfiguration,
        public string $id,
    ) {
        $redisPrefix = $redisConfiguration
            ->connectionPoolConfiguration
            ->get($this->sessionConfiguration->redisConnectionPool)
            ->prefix
        ;

        $this->redis = $this
            ->redisConnectionPoolRepository
            ->getConnection($this->sessionConfiguration->redisConnectionPool)
        ;
        $this->redis->setOption(Redis::OPT_PREFIX, $redisPrefix.'session:');
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);

        $this->data = new Map($this->restoreSessionData());
    }

    public function __destruct()
    {
        Event::defer(function (): void {
            $this->redisConnectionPoolRepository->putConnection(
                $this->sessionConfiguration->redisConnectionPool,
                $this->redis,
            );
        });
    }

    public function persist(): void
    {
        /**
         * Because igbinary serves as a serializer, anything compatible with
         * igbinary can be provided as an argument to Redis::set, including
         * arrays, objects, and so on.
         *
         * @psalm-suppress InvalidArgument
         * @psalm-suppress InvalidCast
         */
        $this->redis->set($this->id, $this->data->toArray());
    }

    private function getPersistedData(): mixed
    {
        return $this->redis->get($this->id);
    }

    private function restoreSessionData(): array
    {
        $stored = $this->getPersistedData();

        if (!is_array($stored)) {
            return [];
        }

        return $stored;
    }
}
