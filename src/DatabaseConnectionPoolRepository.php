<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use OutOfBoundsException;
use PDO;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOProxy;

readonly class DatabaseConnectionPoolRepository
{
    /**
     * @var Map<string,PDOPool>
     */
    public Map $databaseConnectionPool;

    public function __construct(public EventDispatcherInterface $eventDispatcher)
    {
        $this->databaseConnectionPool = new Map();
    }

    public function getConnection(string $name): PDO|PDOProxy
    {
        if (!$this->databaseConnectionPool->hasKey($name)) {
            throw new OutOfBoundsException(sprintf(
                'Database connection pool is not configured: "%s". Available connection pools: "%s"',
                $name,
                $this->databaseConnectionPool->keys()->join('", "'),
            ));
        }

        /**
         * @var PDO|PDOProxy
         */
        return $this->databaseConnectionPool->get($name)->get();
    }

    public function putConnection(string $name, PDO|PDOProxy $pdo): void
    {
        $this->databaseConnectionPool->get($name)->put($pdo);
    }
}
