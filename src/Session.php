<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class Session
{
    public Map $data;

    public function __construct(
        private RedisConfiguration $redisConfiguration,
        private RedisConnectionPoolRepositoryInterface $redisConnectionPoolRepository,
        private SerializerInterface $serializer,
        private SessionConfiguration $sessionConfiguration,
        public string $id,
    ) {

        $this->data = new Map($this->restoreSessionData());
    }

    public function persist(): void
    {
        $redisConnection = new RedisConnection(
            $this->redisConnectionPoolRepository,
            $this->getRedisPrefix(),
        );

        $redisConnection->redis->set(
            $this->id,
            $this->serializer->serialize($this->data->toArray())
        );
    }

    private function getPersistedData(): mixed
    {
        $redisConnection = new RedisConnection(
            $this->redisConnectionPoolRepository,
            $this->getRedisPrefix(),
        );

        $storedValue = $redisConnection->redis->get($this->id);

        if (!is_string($storedValue) || empty($storedValue)) {
            return null;
        }

        return $this->serializer->unserialize($storedValue);
    }

    private function getRedisPrefix(): string
    {
        $redisPrefix = $this->redisConfiguration
            ->connectionPoolConfiguration
            ->get($this->sessionConfiguration->redisConnectionPool)
            ->prefix
        ;

        return $redisPrefix.'.session';
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
