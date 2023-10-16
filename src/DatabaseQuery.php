<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TResult
 *
 * @template-implements DatabaseQueryInterface<TResult>
 */
abstract readonly class DatabaseQuery implements DatabaseQueryInterface
{
    public function __construct(
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
    ) {}

    public function isIterable(): bool
    {
        return false;
    }

    protected function getConnection(string $name = 'default'): DatabaseConnection
    {
        return new DatabaseConnection($this->databaseConnectionPoolRepository, $name);
    }
}
