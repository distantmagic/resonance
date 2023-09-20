<?php

declare(strict_types=1);

namespace Resonance;

use Doctrine\SqlFormatter\SqlFormatter;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;
use Swoole\Database\PDOProxy;
use Swoole\Database\PDOStatementProxy;

readonly class DatabasePreparedStatement
{
    public function __construct(
        private LoggerInterface $logger,
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
        /**
         * Psalm has issues with determining const value types.
         * DM_DB_LOG_QUERIES is bool, but Psalm really thinks it's 'false'.
         * There is no way to change the global const types in psalm config
         * at the moment.
         *
         * @psalm-suppress TypeDoesNotContainType
         */
        if (DM_DB_LOG_QUERIES) {
            $this->logQuery();
        }

        /**
         * @var bool
         */
        $result = $this->pdoStatement->execute();

        if (!$result) {
            throw new PDOException($this->pdoStatement->errorInfo());
        }

        return new DatabaseExecutedStatement($this->pdo, $this->pdoStatement);
    }

    private function logQuery(): void
    {
        $formatter = new SqlFormatter();

        $this->logger->debug($formatter->format($this->sql));
    }
}
