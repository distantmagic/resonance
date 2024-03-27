<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use PDO;
use PDOStatement;
use Swoole\Database\PDOStatementProxy;

readonly class DatabasePreparedStatement implements Statement
{
    public function __construct(
        private DatabaseQueryLogger $databaseQueryLogger,
        private PDOStatement|PDOStatementProxy $pdoStatement,
        private string $sql,
    ) {}

    public function bindValue($param, $value, ParameterType $type): void
    {
        $this->pdoStatement->bindValue(
            $param,
            $value,
            $this->convertParamType($type),
        );
    }

    public function execute(): DatabaseExecutedStatement
    {
        $this->databaseQueryLogger->onQueryBeforeExecute($this->sql);

        /**
         * @var bool
         */
        $result = $this->pdoStatement->execute();

        if (!$result) {
            throw new PDOException($this->pdoStatement->errorInfo());
        }

        return new DatabaseExecutedStatement($this->pdoStatement);
    }

    /**
     * @psalm-return PDO::PARAM_*
     */
    private function convertParamType(ParameterType $type): int
    {
        return match ($type) {
            ParameterType::NULL => PDO::PARAM_NULL,
            ParameterType::INTEGER => PDO::PARAM_INT,
            ParameterType::STRING,
            ParameterType::ASCII => PDO::PARAM_STR,
            ParameterType::BINARY,
            ParameterType::LARGE_OBJECT => PDO::PARAM_LOB,
            ParameterType::BOOLEAN => PDO::PARAM_BOOL,
        };
    }
}
