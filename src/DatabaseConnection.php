<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\DBAL\Driver\Connection;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;
use Swoole\Database\PDOProxy;
use Swoole\Database\PDOStatementProxy;
use Swoole\Event;

readonly class DatabaseConnection implements Connection
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

    public function beginTransaction(): void
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $result = $this->pdo->beginTransaction();

        $this->assertNotFalse($result);
    }

    public function commit(): void
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $result = $this->pdo->commit();

        $this->assertNotFalse($result);
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

    public function lastInsertId(): int|string
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         *
         * @var false|string $lastInsertId
         */
        $lastInsertId = $this->pdo->lastInsertId();

        return $this->assertNotFalse($lastInsertId);
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

    public function quote(string $value): string
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $quoted = $this->pdo->quote($value);

        return $this->assertNotFalse($quoted);
    }

    public function rollBack(): void
    {
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $result = $this->pdo->rollBack();

        $this->assertNotFalse($result);
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
