<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Log\LoggerInterface;

/**
 * @template TResult
 *
 * @template-implements DatabaseQueryInterface<TResult>
 */
abstract readonly class DatabaseQuery implements DatabaseQueryInterface
{
    public function __construct(
        private DatabaseConfiguration $databaseConfiguration,
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private LoggerInterface $logger,
    ) {}

    public function isIterable(): bool
    {
        return false;
    }

    /**
     * @param non-empty-string $name
     */
    protected function getConnection(string $name = 'default'): DatabaseConnection
    {
        return new DatabaseConnection(
            $this->databaseConfiguration,
            $this->databaseConnectionPoolRepository,
            $this->logger,
            $name,
        );
    }
}
