<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\SqlFormatter\SqlFormatter;
use Psr\Log\LoggerInterface;

readonly class DatabaseQueryLogger
{
    private SqlFormatter $sqlFormatter;

    public function __construct(
        private DatabaseConnectionPoolConfiguration $databaseConnectionPoolConfiguration,
        private LoggerInterface $logger,
    ) {
        $this->sqlFormatter = new SqlFormatter();
    }

    public function onQueryBeforeExecute(string $sql): void
    {
        if ($this->databaseConnectionPoolConfiguration->logQueries) {
            $this->logger->debug($this->sqlFormatter->format($sql));
        }
    }
}
