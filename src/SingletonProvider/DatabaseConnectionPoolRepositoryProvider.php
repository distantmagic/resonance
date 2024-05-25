<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

/**
 * @template-extends SingletonProvider<DatabaseConnectionPoolRepository>
 */
#[RequiresPhpExtension('pdo')]
#[Singleton(provides: DatabaseConnectionPoolRepository::class)]
final readonly class DatabaseConnectionPoolRepositoryProvider extends SingletonProvider
{
    public function __construct(
        private DatabaseConfiguration $databaseConfiguration,
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

            if (is_string($connectionPoolConfiguration->password)) {
                $pdoConfig->withPassword($connectionPoolConfiguration->password);
            }

            $pdoPool = new PDOPool(
                $pdoConfig,
                $connectionPoolConfiguration->poolSize,
            );

            $databaseConnectionPoolRepository->databaseConnectionPool->put($name, $pdoPool);
        }

        return $databaseConnectionPoolRepository;
    }
}
