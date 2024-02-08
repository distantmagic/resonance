<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\PDOPool;
use Distantmagic\Resonance\PDOPoolConnectionBuilderCollection;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Swoole\Database\PDOConfig;

/**
 * @template-extends SingletonProvider<DatabaseConnectionPoolRepository>
 */
#[Singleton(provides: DatabaseConnectionPoolRepository::class)]
final readonly class DatabaseConnectionPoolRepositoryProvider extends SingletonProvider
{
    public function __construct(
        private DatabaseConfiguration $databaseConfiguration,
        private PDOPoolConnectionBuilderCollection $pdoPoolConnectionBuilderCollection,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): DatabaseConnectionPoolRepository
    {
        $databaseConnectionPoolRepository = new DatabaseConnectionPoolRepository();

        foreach ($this->databaseConfiguration->connectionPoolConfiguration as $name => $connectionPoolConfiguration) {
            $pdoConfig = new PDOConfig();

            if (is_string($connectionPoolConfiguration->host)) {
                $pdoConfig->withHost($connectionPoolConfiguration->host);
                $pdoConfig->withPort($connectionPoolConfiguration->port);
            }

            if (is_string($connectionPoolConfiguration->unixSocket)) {
                $pdoConfig->withUnixSocket($connectionPoolConfiguration->unixSocket);
            }

            $pdoConfig->withDbName($connectionPoolConfiguration->database);
            $pdoConfig->withDriver($connectionPoolConfiguration->driver->value);
            $pdoConfig->withUsername($connectionPoolConfiguration->username);
            $pdoConfig->withPassword($connectionPoolConfiguration->password);

            $pdoPool = new PDOPool(
                $this->pdoPoolConnectionBuilderCollection->getBuildersForConnection($name),
                $connectionPoolConfiguration,
                $pdoConfig,
            );

            if ($connectionPoolConfiguration->poolPrefill) {
                $pdoPool->fill();
            }

            $databaseConnectionPoolRepository->databaseConnectionPool->put($name, $pdoPool);
        }

        return $databaseConnectionPoolRepository;
    }
}
