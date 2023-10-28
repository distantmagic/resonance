<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PDO;
use PDOStatement;
use RuntimeException;
use Swoole\Database\PDOProxy;
use Swoole\Database\PDOStatementProxy;
use Swoole\Event;

readonly class DatabaseConnection
{
    private PDO|PDOProxy $pdo;

    public function __construct(
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private string $connectionPoolName = 'default',
    ) {
        $this->pdo = $this->databaseConnectionPoolRepository->getConnection($this->connectionPoolName);
    }

    public function __destruct()
    {
        Event::defer(function () {
            $this->databaseConnectionPoolRepository->putConnection($this->connectionPoolName, $this->pdo);
        });
    }

    public function lastInsertId(): false|string
    {
        $lastInsertId = $this->pdo->lastInsertId();

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

        if (!$pdoPreparedStatement) {
            throw new PDOException($this->pdo->errorInfo());
        }

        return new DatabasePreparedStatement(
            $this->databaseConnectionPoolRepository->eventDispatcher,
            $pdoPreparedStatement,
            $sql,
        );
    }
}
