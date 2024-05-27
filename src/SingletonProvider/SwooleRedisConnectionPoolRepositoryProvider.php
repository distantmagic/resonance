<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresBackendDriver;
use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackendDriver;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\RedisConnectionPoolRepository;
use Distantmagic\Resonance\RedisConnectionPoolRepositoryInterface;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

/**
 * @template-extends SingletonProvider<RedisConnectionPoolRepositoryInterface>
 */
#[RequiresBackendDriver(BackendDriver::Swoole)]
#[RequiresPhpExtension('redis')]
#[Singleton(provides: RedisConnectionPoolRepositoryInterface::class)]
final readonly class SwooleRedisConnectionPoolRepositoryProvider extends SingletonProvider
{
    public function __construct(
        private RedisConfiguration $databaseConfiguration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RedisConnectionPoolRepositoryInterface
    {
        $redisConnectionPoolRepository = new RedisConnectionPoolRepository();

        foreach ($this->databaseConfiguration->connectionPoolConfiguration as $name => $connectionPoolConfiguration) {
            $redisConfig = (new RedisConfig())
                ->withHost($connectionPoolConfiguration->host)
                ->withPort($connectionPoolConfiguration->port)
                ->withAuth($connectionPoolConfiguration->password)
                ->withDbIndex($connectionPoolConfiguration->dbIndex)
                ->withTimeout($connectionPoolConfiguration->timeout)
            ;

            $redisPool = new RedisPool($redisConfig, $connectionPoolConfiguration->poolSize);
            $redisConnectionPoolRepository->redisConnectionPool->put($name, $redisPool);
        }

        return $redisConnectionPoolRepository;
    }
}
