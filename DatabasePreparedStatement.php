<?php

declare(strict_types=1);

namespace Resonance;

use PDO;
use PDOStatement;
use Resonance\Event\SQLQueryBeforeExecute;
use Swoole\Database\PDOProxy;
use Swoole\Database\PDOStatementProxy;

readonly class DatabasePreparedStatement
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private PDO|PDOProxy $pdo,
        private PDOStatement|PDOStatementProxy $pdoStatement,
        private string $sql,
    ) {}

    public function bindValue(int|string $param, int|string $value, int $type = PDO::PARAM_STR): self
    {
        $this->pdoStatement->bindValue($param, $value, $type);

        return $this;
    }

    public function execute(): DatabaseExecutedStatement
    {
        $this->eventDispatcher->dispatch(new SQLQueryBeforeExecute($this->sql));

        /**
         * @var bool
         */
        $result = $this->pdoStatement->execute();

        if (!$result) {
            throw new PDOException($this->pdoStatement->errorInfo());
        }

        return new DatabaseExecutedStatement($this->pdo, $this->pdoStatement);
    }
}
