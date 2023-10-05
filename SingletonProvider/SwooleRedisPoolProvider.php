<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Singleton;
use Resonance\PHPProjectFiles;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

/**
 * @template-extends SingletonProvider<RedisPool>
 */
#[Singleton(provides: RedisPool::class)]
final readonly class SwooleRedisPoolProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RedisPool
    {
        return new RedisPool($this->configFromGlobals());
    }

    public function shouldRegister(): bool
    {
        return !empty(DM_REDIS_HOST);
    }

    private function configFromGlobals(): RedisConfig
    {
        $redisConfig = new RedisConfig();

        return $redisConfig
            ->withHost(DM_REDIS_HOST)
            ->withPort(DM_REDIS_PORT)
            ->withAuth(DM_REDIS_PASSWORD)
            ->withDbIndex(0)
            ->withTimeout(1)
        ;
    }
}
