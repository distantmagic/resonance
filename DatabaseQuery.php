<?php

declare(strict_types=1);

namespace Resonance;

use Swoole\Database\PDOPool;

/**
 * @template TResult
 *
 * @template-implements DatabaseQueryInterface<TResult>
 */
abstract readonly class DatabaseQuery implements DatabaseQueryInterface
{
    protected DatabaseConnection $dblayer;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PDOPool $pdoPool,
    ) {
        $this->dblayer = new DatabaseConnection($eventDispatcher, $pdoPool);
    }

    public function isIterable(): bool
    {
        return false;
    }
}
