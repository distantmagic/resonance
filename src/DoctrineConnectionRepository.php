<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Logging\Driver as LoggingDriver;
use Ds\Map;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Http\Request;
use WeakMap;

/**
 * We are implementing a custom driver to be used internally by Doctrine.
 *
 * @psalm-suppress InternalMethod
 */
#[GrantsFeature(Feature::Doctrine)]
#[Singleton]
readonly class DoctrineConnectionRepository
{
    /**
     * @var WeakMap<Request,Map<string,Connection>>
     */
    public WeakMap $connections;

    public function __construct(
        private Configuration $configuration,
        private DatabaseConfiguration $databaseConfiguration,
        private DoctrineMySQLDriver $doctrineMySQLDriver,
        private DoctrinePostgreSQLDriver $doctrinePostgreSQLDriver,
        private DoctrineSQLiteDriver $doctrineSQLiteDriver,
        private EventManager $eventManager,
        private LoggerInterface $logger,
    ) {
        /**
         * @var WeakMap<Request,Map<string,Connection>>
         */
        $this->connections = new WeakMap();
    }

    /**
     * @param non-empty-string $name
     */
    public function buildConnection(string $name = 'default'): Connection
    {
        return new Connection(
            config: $this->configuration,
            driver: $this->getDriver($name),
            eventManager: $this->eventManager,
            params: [
                'driverOptions' => [
                    'connectionPoolName' => $name,
                ],
            ],
        );
    }

    /**
     * @param non-empty-string $name
     */
    public function getConnection(Request $request, string $name = 'default'): Connection
    {
        if (!$this->connections->offsetExists($request)) {
            $this->connections->offsetSet($request, new Map());
        }

        $connectionsMap = $this->connections->offsetGet($request);

        if ($connectionsMap->hasKey($name)) {
            return $connectionsMap->get($name);
        }

        $conn = $this->buildConnection($name);

        $connectionsMap->put($name, $conn);

        return $conn;
    }

    /**
     * @param non-empty-string $name
     */
    private function getDriver(string $name): Driver
    {
        $poolConfiguration = $this->databaseConfiguration->connectionPoolConfiguration->get($name, null);

        if (is_null($poolConfiguration)) {
            throw new RuntimeException(sprintf('Connection pool "%s" not found', $name));
        }

        $driver = match ($poolConfiguration->driver) {
            DatabaseConnectionPoolDriverName::MySQL => $this->doctrineMySQLDriver,
            DatabaseConnectionPoolDriverName::PostgreSQL => $this->doctrinePostgreSQLDriver,
            DatabaseConnectionPoolDriverName::SQLite => $this->doctrineSQLiteDriver,
        };

        if ($poolConfiguration->logQueries) {
            return new LoggingDriver($driver, $this->logger);
        }

        return $driver;
    }
}
