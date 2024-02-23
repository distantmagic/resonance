<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\DBAL\Driver\PDO\ParameterTypeMap;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\ParameterType;
use LogicException;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;
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
    private DatabaseQueryLogger $databaseQueryLogger;
    private PDO|PDOProxy $pdo;

    /**
     * @param non-empty-string $connectionPoolName
     */
    public function __construct(
        DatabaseConfiguration $databaseConfiguration,
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        LoggerInterface $logger,
        private string $connectionPoolName = 'default',
    ) {
        $this->databaseQueryLogger = new DatabaseQueryLogger(
            $databaseConfiguration->connectionPoolConfiguration->get($connectionPoolName),
            $logger,
        );
        $this->pdo = $this
            ->databaseConnectionPoolRepository
            ->getConnection($this->connectionPoolName)
        ;
    }

    public function __destruct()
    {
        Event::defer(function (): void {
            $this->databaseConnectionPoolRepository->putConnection($this->connectionPoolName, $this->pdo);
        });
    }

    public function beginTransaction(): bool
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $result = $this->pdo->beginTransaction();
        $this->assertNotFalse($result);

        return true;
    }

    public function commit(): bool
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $result = $this->pdo->commit();
        $this->assertNotFalse($result);

        return true;
    }

    /**
     * @psalm-taint-sink sql $sql
     */
    public function exec(string $sql): int
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
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
         * @psalm-suppress UndefinedMagicMethod
         *
         * @var false|string
         */
        $version = $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        return $this->assertNotFalse($version);
    }

    public function lastInsertId($name = null): false|string
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         *
         * @var false|string $lastInsertId
         */
        $lastInsertId = $this->pdo->lastInsertId($name);

        if (false === $lastInsertId) {
            return false;
        }

        return $lastInsertId;
    }

    public function prepare(string $sql): DatabasePreparedStatement
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         *
         * @var false|PDOStatement|PDOStatementProxy
         */
        $pdoPreparedStatement = $this->pdo->prepare($sql);
        $pdoPreparedStatement = $this->assertNotFalse($pdoPreparedStatement);

        return new DatabasePreparedStatement(
            $this->databaseQueryLogger,
            $pdoPreparedStatement,
            $sql,
        );
    }

    public function query(string $sql): DatabaseExecutedStatement
    {
        $this->databaseQueryLogger->onQueryBeforeExecute($sql);

        /**
         * @psalm-suppress UndefinedMagicMethod
         *
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

        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        return $this->pdo->quote($value, ParameterTypeMap::convertParamType($type));
    }

    public function rollBack(): bool
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
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
            /**
             * @psalm-suppress UndefinedMagicMethod
             */
            throw new PDOException($this->pdo->errorInfo());
        }

        return $value;
    }
}
