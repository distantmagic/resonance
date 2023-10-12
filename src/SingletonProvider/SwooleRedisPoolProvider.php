<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

/**
 * @template-extends SingletonProvider<RedisPool>
 */
#[Singleton(provides: RedisPool::class)]
final readonly class SwooleRedisPoolProvider extends SingletonProvider
{
    public function __construct(private RedisConfig $redisConfig) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RedisPool
    {
        return new RedisPool($this->redisConfig);
    }
}
