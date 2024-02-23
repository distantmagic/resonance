<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use Psr\Log\LoggerInterface;
use SensitiveParameter;

trait DoctrineDriverConnectTrait
{
    public function __construct(
        private DatabaseConfiguration $databaseConfiguration,
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private LoggerInterface $logger,
    ) {}

    public function connect(
        #[SensitiveParameter]
        array $params
    ): DatabaseConnection {
        if (!isset($params['driverOptions'])) {
            throw new LogicException('Expected driverOptions parameter to be set');
        }

        /**
         * @var mixed explicitly mixed for typechecks
         */
        $driverOptions = $params['driverOptions'];

        if (!is_array($driverOptions)) {
            throw new LogicException('Expected driverOptions.connectionPoolName to be an array');
        }

        if (!isset($driverOptions['connectionPoolName'])) {
            throw new LogicException('Expected driverOptions.connectionPoolName parameter');
        }

        /**
         * @var mixed explicitly mixed for typechecks
         */
        $connectionPoolName = $driverOptions['connectionPoolName'];

        if (!is_string($connectionPoolName) || empty($connectionPoolName)) {
            throw new LogicException('Expected driverOptions.connectionPoolName to be a non-empty-string');
        }

        return new DatabaseConnection(
            $this->databaseConfiguration,
            $this->databaseConnectionPoolRepository,
            $this->logger,
            $connectionPoolName,
        );
    }
}
