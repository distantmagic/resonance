<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\DBAL\Driver\PDO\ParameterTypeMap;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\ParameterType;
use LogicException;
use PDO;
use PDOStatement;
use RuntimeException;
use Swoole\Database\PDOProxy;
use Swoole\Database\PDOStatementProxy;
use Swoole\Event;

/**
 * This interface is going to be mandatory in the next Doctrine release, but
 * for now it has to be used.
 *
 * @psalm-suppress DeprecatedInterface
 */
readonly class DatabaseConnection implements ServerInfoAwareConnection
{
    private PDO|PDOProxy $pdo;

    public function __construct(
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private string $connectionPoolName = 'default',
    ) {
        $this->pdo = $this->databaseConnectionPoolRepository->getConnection($this->connectionPoolName);

        /**
         * Make sure the errmode is silent. Otherwise the connection pool won't
         * replenish itself.
         */
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    public function __destruct()
    {
        Event::defer(function () {
            $this->databaseConnectionPoolRepository->putConnection($this->connectionPoolName, $this->pdo);
        });
    }

    public function beginTransaction(): bool
    {
        /**
         * @var bool $result
         */
        $result = $this->pdo->beginTransaction();
        $this->assertNotFalse($result);

        return true;
    }

    public function commit(): bool
    {
        /**
         * @var bool $result
         */
        $result = $this->pdo->commit();
        $this->assertNotFalse($result);

        return true;
    }

    public function exec(string $sql): int
    {
        /**
         * @var false|int
         */
        $result = $this->pdo->exec($sql);

        return $this->assertNotFalse($result);
    }

    public function getNativeConnection(): PDO|PDOProxy
    {
        return $this->pdo;
    }

    public function getServerVersion(): string
    {
        /**
         * @var false|string
         */
        $version = $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        return $this->assertNotFalse($version);
    }

    public function lastInsertId($name = null): false|string
    {
        $lastInsertId = $this->pdo->lastInsertId($name);

        if (false === $lastInsertId) {
            return false;
        }

        if (!is_string($lastInsertId)) {
            throw new RuntimeException('Last insert id is not a string');
        }

        return $lastInsertId;
    }

    public function prepare(string $sql): DatabasePreparedStatement
    {
        /**
         * @var false|PDOStatement|PDOStatementProxy
         */
        $pdoPreparedStatement = $this->pdo->prepare($sql);
        $pdoPreparedStatement = $this->assertNotFalse($pdoPreparedStatement);

        return new DatabasePreparedStatement(
            $this->databaseConnectionPoolRepository->eventDispatcher,
            $pdoPreparedStatement,
            $sql,
        );
    }

    public function query(string $sql): DatabaseExecutedStatement
    {
        /**
         * @var false|PDOStatement|PDOStatementProxy
         */
        $result = $this->pdo->query($sql);
        $result = $this->assertNotFalse($result);

        return new DatabaseExecutedStatement($result);
    }

    /**
     * @psalm-assert ParameterType::* $type
     *
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public function quote($value, $type = ParameterType::STRING)
    {
        if (!is_string($value)) {
            throw new LogicException('Only string values can be quoted');
        }

        return $this->pdo->quote($value, ParameterTypeMap::convertParamType($type));
    }

    public function rollBack(): bool
    {
        /**
         * @var bool $result
         */
        $result = $this->pdo->rollBack();
        $this->assertNotFalse($result);

        return true;
    }

    /**
     * @template TValue
     *
     * @param false|TValue $value
     *
     * @return TValue
     */
    private function assertNotFalse(mixed $value): mixed
    {
        if (false === $value) {
            throw new PDOException($this->pdo->errorInfo());
        }

        return $value;
    }
}
