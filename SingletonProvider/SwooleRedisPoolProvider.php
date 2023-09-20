<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Singleton;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @template-extends SingletonProvider<RedisPool>
 */
#[Singleton(provides: RedisPool::class)]
final readonly class SwooleRedisPoolProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, ?ConsoleOutputInterface $output = null): RedisPool
    {
        return new RedisPool($this->configFromGlobals());
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
