<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Event\SQLQueryBeforeExecute;
use Doctrine\DBAL\Driver\PDO\ParameterTypeMap;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use LogicException;
use PDOStatement;
use Swoole\Database\PDOStatementProxy;

readonly class DatabasePreparedStatement implements Statement
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private PDOStatement|PDOStatementProxy $pdoStatement,
        private string $sql,
    ) {}

    /**
     * @param mixed $param         explicitly mixed for typechecks
     * @param mixed $variable      explicitly mixed for typechecks
     * @param mixed $type          explicitly mixed for typechecks
     * @param mixed $length        explicitly mixed for typechecks
     * @param mixed $driverOptions explicitly mixed for typechecks
     */
    public function bindParam(
        $param,
        &$variable,
        $type = ParameterType::STRING,
        $length = null,
        $driverOptions = null
    ): never {
        throw new LogicException('Use bindValue() instead');
    }

    /**
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        return $this->pdoStatement->bindValue(
            $param,
            $value,
            ParameterTypeMap::convertParamType($type),
        );
    }

    public function execute($params = null): DatabaseExecutedStatement
    {
        $this->eventDispatcher->dispatch(new SQLQueryBeforeExecute($this->sql));

        /**
         * @var bool
         */
        $result = $this->pdoStatement->execute();

        if (!$result) {
            throw new PDOException($this->pdoStatement->errorInfo());
        }

        return new DatabaseExecutedStatement($this->pdoStatement);
    }
}
