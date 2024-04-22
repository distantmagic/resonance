<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Redis;

readonly class Session
{
    public Map $data;

    public function __construct(
        private RedisConfiguration $redisConfiguration,
        private RedisConnectionPoolRepository $redisConnectionPoolRepository,
        private SerializerInterface $serializer,
        private SessionConfiguration $sessionConfiguration,
        public string $id,
    ) {

        $this->data = new Map($this->restoreSessionData());
    }

    public function persist(): void
    {
        $this->getRedisConnection()->set(
            $this->id,
            $this->serializer->serialize($this->data->toArray())
        );
    }

    private function getPersistedData(): mixed
    {
        $storedValue = $this->getRedisConnection()->get($this->id);

        if (!is_string($storedValue) || empty($storedValue)) {
            return null;
        }

        return $this->serializer->unserialize($storedValue);
    }

    private function getRedisConnection(): Redis
    {
        $redisPrefix = $this->redisConfiguration
            ->connectionPoolConfiguration
            ->get($this->sessionConfiguration->redisConnectionPool)
            ->prefix
        ;

        $redisConnection = new RedisConnection(
            redisConnectionPoolRepository: $this->redisConnectionPoolRepository,
            redisPrefix: $redisPrefix.'session:',
        );

        return $redisConnection->redis;
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
