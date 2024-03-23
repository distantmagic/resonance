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
        private SerializerInterface $serializer,
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
        $this->redis->set(
            $this->id,
            $this->serializer->serialize($this->data->toArray())
        );
    }

    private function getPersistedData(): mixed
    {
        $storedValue = $this->redis->get($this->id);

        if (!is_string($storedValue) || empty($storedValue)) {
            return null;
        }

        return $this->serializer->unserialize($storedValue);
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
