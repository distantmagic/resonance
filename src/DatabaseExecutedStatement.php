<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use PDO;
use PDOStatement;
use RuntimeException;
use Swoole\Database\PDOProxy;
use Swoole\Database\PDOStatementProxy;

readonly class DatabaseExecutedStatement
{
    public function __construct(
        private PDO|PDOProxy $pdo,
        private PDOStatement|PDOStatementProxy $pdoStatement,
    ) {}

    public function __destruct()
    {
        $this->pdoStatement->closeCursor();
    }

    /**
     * In all cases, false is returned on failure or if there are no more rows.
     */
    public function fetchAssoc(): array|false
    {
        $ret = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);

        if (false === $ret) {
            return $ret;
        }

        if (!is_array($ret)) {
            throw new RuntimeException('FETCH_ASSOC returned something else than array');
        }

        return $ret;
    }

    public function first(): ?array
    {
        try {
            $data = $this->fetchAssoc();

            if (false === $data) {
                return null;
            }

            return $data;
        } finally {
            $this->pdoStatement->closeCursor();
        }
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

    public function yieldAssoc(): Generator
    {
        while (true) {
            $data = $this->fetchAssoc();

            if (false === $data) {
                break;
            }

            yield $data;
        }
    }
}
