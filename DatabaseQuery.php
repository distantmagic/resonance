<?php

declare(strict_types=1);

namespace Resonance;

use Psr\Log\LoggerInterface;
use Swoole\Database\PDOPool;

/**
 * @template TResult
 *
 * @template-implements DatabaseQueryInterface<TResult>
 */
abstract readonly class DatabaseQuery implements DatabaseQueryInterface
{
    protected DatabaseConnection $dblayer;

    public function __construct(LoggerInterface $logger, PDOPool $pdoPool)
    {
        $this->dblayer = new DatabaseConnection($logger, $pdoPool);
    }

    public function isIterable(): bool
    {
        return false;
    }
}
