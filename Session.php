<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Redis;
use RuntimeException;
use Swoole\Database\RedisPool;

use function Swoole\Coroutine\go;

readonly class Session
{
    public Map $data;
    private Redis $redis;

    public function __construct(
        private RedisPool $redisPool,
        public string $id,
    ) {
        $this->redis = $this->redisPool->get();
        $this->redis->setOption(Redis::OPT_PREFIX, DM_REDIS_PREFIX.'session:');
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);

        $this->data = new Map($this->restoreSessionData());
    }

    public function __destruct()
    {
        $cid = go(function () {
            $this->redisPool->put($this->redis);
        });

        if (!is_int($cid)) {
            throw new RuntimeException('Unable to return connection back to the redis pool.');
        }
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
