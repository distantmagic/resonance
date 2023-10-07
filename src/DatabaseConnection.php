<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PDO;
use PDOStatement;
use RuntimeException;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOProxy;
use Swoole\Database\PDOStatementProxy;

use function Swoole\Coroutine\go;

readonly class DatabaseConnection
{
    private PDO|PDOProxy $pdo;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private PDOPool $pdoPool,
    ) {
        /**
         * @var PDO|PDOProxy
         */
        $this->pdo = $this->pdoPool->get();

        // Sometimes Swoole PDO wrapper can't reconnect properly when
        // interrupted with an exception. Silent mode should be used instead.
        // All errors should be handled outside of the PDO wrapper.
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    public function __destruct()
    {
        $cid = go(function () {
            $this->pdoPool->put($this->pdo);
        });

        if (!is_int($cid)) {
            throw new RuntimeException('Unable to return connection back to the PDO pool.');
        }
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
            $this->eventDispatcher,
            $this->pdo,
            $pdoPreparedStatement,
            $sql,
        );
    }
}
