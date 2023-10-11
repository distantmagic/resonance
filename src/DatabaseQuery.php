<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Database\PDOPool;

/**
 * @template TResult
 *
 * @template-implements DatabaseQueryInterface<TResult>
 */
abstract readonly class DatabaseQuery implements DatabaseQueryInterface
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected PDOPool $pdoPool,
    ) {}

    public function isIterable(): bool
    {
        return false;
    }

    protected function getConnection(): DatabaseConnection
    {
        return new DatabaseConnection($this->eventDispatcher, $this->pdoPool);
    }
}
