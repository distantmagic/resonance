<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\DBAL\Driver\Result;
use PDO;
use PDOStatement;
use RuntimeException;
use Swoole\Database\PDOStatementProxy;

readonly class DatabaseExecutedStatement implements Result
{
    public function __construct(
        private PDOStatement|PDOStatementProxy $pdoStatement,
    ) {}

    public function __destruct()
    {
        $this->free();
    }

    public function columnCount(): int
    {
        /**
         * @var int
         */
        return $this->pdoStatement->columnCount();
    }

    public function fetchAllAssociative(): array
    {
        /**
         * @var list<array<string,mixed>>
         */
        return $this->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllNumeric(): array
    {
        /**
         * @var list<list<mixed>>
         */
        return $this->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * In all cases, false is returned on failure or if there are no more rows.
     */
    public function fetchAssociative(): array|false
    {
        /**
         * @var array<string,mixed>|false
         */
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchFirstColumn(): array
    {
        /**
         * @var list<mixed>
         */
        return $this->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return false|list<mixed>
     */
    public function fetchNumeric(): array|false
    {
        /**
         * @var false|list<mixed>
         */
        return $this->fetch(PDO::FETCH_NUM);
    }

    public function fetchOne(): mixed
    {
        return $this->fetch(PDO::FETCH_COLUMN);
    }

    public function free(): void
    {
        $this->pdoStatement->closeCursor();
    }

    public function rowCount(): int
    {
        /**
         * @var int
         */
        return $this->pdoStatement->rowCount();
    }

    /**
     * @psalm-param PDO::FETCH_* $mode
     */
    private function fetch(int $mode): array|false|string
    {
        $ret = $this->pdoStatement->fetch($mode);

        if (false === $ret) {
            return $ret;
        }

        if (!is_array($ret) && !is_string($ret)) {
            throw new RuntimeException('Fetch returned something else than array or string');
        }

        return $ret;
    }

    /**
     * @psalm-param PDO::FETCH_* $mode
     */
    private function fetchAll(int $mode): array
    {
        $ret = $this->pdoStatement->fetchAll($mode);

        if (!is_array($ret)) {
            throw new RuntimeException('FetchAll returned something else than array');
        }

        return $ret;
    }
}
