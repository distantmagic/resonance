<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\HttpResponseReady;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Logging\Driver as LoggingDriver;
use Ds\Map;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request;
use WeakMap;

/**
 * We are implementing a custom driver to be used internally by Doctrine.
 *
 * @psalm-suppress InternalMethod
 *
 * @template-extends EventListener<HttpResponseReady,void>
 */
#[Singleton]
readonly class DoctrineConnectionRepository extends EventListener
{
    /**
     * @var WeakMap<Request,Map<string,Connection>>
     */
    private WeakMap $connections;

    public function __construct(
        private Configuration $configuration,
        private DatabaseConfiguration $databaseConfiguration,
        private DoctrineMySQLDriver $doctrineMySQLDriver,
        private DoctrinePostgreSQLDriver $doctrinePostgreSQLDriver,
        private DoctrineSQLiteDriver $doctrineSQLiteDriver,
        private EventListenerAggregate $eventListenerAggregate,
        private LoggerInterface $logger,
    ) {
        /**
         * @var WeakMap<Request,Map<string,Connection>>
         */
        $this->connections = new WeakMap();

        /**
         * False positive, $this IS an EventListenerInterface
         *
         * @psalm-suppress InvalidArgument
         */
        $this->eventListenerAggregate->addListener(HttpResponseReady::class, $this);
    }

    public function __destruct()
    {
        /**
         * False positive, $this IS an EventListenerInterface
         *
         * @psalm-suppress InvalidArgument
         */
        $this->eventListenerAggregate->removeListener(HttpResponseReady::class, $this);
    }

    public function buildConnection(string $name = 'default'): Connection
    {
        return new Connection(
            [
                'driverOptions' => [
                    'connectionPoolName' => $name,
                ],
            ],
            $this->getDriver($name),
            $this->configuration,
        );
    }

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
     * Since Doctrine caches Repositories and other objects and many of them
     * may hold a connection reference, those connections need to be manually
     * closed and returned to the pool after each request.
     *
     * @param HttpResponseReady $event
     */
    public function handle(EventInterface $event): void
    {
        if (!$this->connections->offsetExists($event->request)) {
            return;
        }

        foreach ($this->connections->offsetGet($event->request) as $connection) {
            /**
             * This doesn't really terminate the connection, instead (as of
             * writing this), internally to Doctrine, it just sets the pointer
             * to that connection to NULL, thus allowing the GC to collect the
             * underlying PDO object.
             *
             * This is exactly the behavior we need.
             *
             * This also seems to be the PDO's recommended way of terminating
             * connections - PDO closes a connection when all references to
             * that connection are gone.
             */
            $connection->close();
        }
    }

    private function getDriver(string $name): Driver
    {
        $poolConfiguration = $this->databaseConfiguration->connectionPoolConfiguration->get($name);

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
