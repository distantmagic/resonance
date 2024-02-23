<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Log\LoggerInterface;

readonly class DatabaseQueryLogger
{
    public function __construct(
        private DatabaseConnectionPoolConfiguration $databaseConnectionPoolConfiguration,
        private LoggerInterface $logger,
    ) {}

    public function onQueryBeforeExecute(string $sql): void
    {
        if ($this->databaseConnectionPoolConfiguration->logQueries) {
            $this->logger->debug($sql);
        }
    }
}
