<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Swoole\Database\RedisConfig;

/**
 * @template-extends SingletonProvider<RedisConfig>
 */
#[Singleton(provides: RedisConfig::class)]
final readonly class SwooleRedisConfigProvider extends SingletonProvider
{
    public function __construct(private RedisConfiguration $redisConfiguration) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RedisConfig
    {
        $redisConfig = new RedisConfig();

        return $redisConfig
            ->withHost($this->redisConfiguration->host)
            ->withPort($this->redisConfiguration->port)
            ->withAuth($this->redisConfiguration->password)
            ->withDbIndex(0)
            ->withTimeout(1)
        ;
    }
}
