<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
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
#[RequiresPhpExtension('redis')]
#[Singleton(provides: RedisConnectionPoolRepository::class)]
final readonly class RedisConnectionPoolRepositoryProvider extends SingletonProvider
{
    public function __construct(
        private RedisConfiguration $databaseConfiguration,
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

            $redisPool = new RedisPool($redisConfig, $connectionPoolConfiguration->poolSize);

            if ($connectionPoolConfiguration->poolPrefill) {
                $redisPool->fill();
            }

            $redisConnectionPoolRepository->redisConnectionPool->put($name, $redisPool);
        }

        return $redisConnectionPoolRepository;
    }
}
