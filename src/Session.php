<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Redis;
use Swoole\Database\RedisPool;
use Swoole\Event;

readonly class Session
{
    public Map $data;
    private Redis $redis;

    public function __construct(
        RedisConfiguration $redisConfiguration,
        private RedisPool $redisPool,
        public string $id,
    ) {
        $this->redis = $this->redisPool->get();
        $this->redis->setOption(Redis::OPT_PREFIX, $redisConfiguration->prefix.'session:');
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);

        $this->data = new Map($this->restoreSessionData());
    }

    public function __destruct()
    {
        Event::defer(function () {
            $this->redisPool->put($this->redis);
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
