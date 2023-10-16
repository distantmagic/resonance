<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\EventDispatcherInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\RedisConnectionPoolRepository;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

/**
 * @template-extends SingletonProvider<RedisConnectionPoolRepository>
 */
#[Singleton(provides: RedisConnectionPoolRepository::class)]
final readonly class RedisConnectionPoolRepositoryProvider extends SingletonProvider
{
    public function __construct(
        private RedisConfiguration $databaseConfiguration,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RedisConnectionPoolRepository
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
            $redisConnectionPoolRepository->redisConnectionPool->put(
                $name,
                new RedisPool($redisConfig),
            );
        }

        return $redisConnectionPoolRepository;
    }
}
