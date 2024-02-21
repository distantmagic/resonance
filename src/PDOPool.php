<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use PDO;
use Swoole\ConnectionPool;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOProxy;

/**
 * @psalm-suppress PropertyNotSetInConstructor swoole internals
 */
class PDOPool extends ConnectionPool
{
    /**
     * @param Set<PDOPoolConnectionBuilderInterface> $connectionBuilders
     */
    public function __construct(
        private Set $connectionBuilders,
        private PDOConfig $config,
        int $size,
    ) {
        parent::__construct(
            $this->createConnection(...),
            $size,
            PDOProxy::class,
        );
    }

    public function get(float $timeout = -1): PDOProxy
    {
        /**
         * @var PDOProxy $pdo
         */
        $pdo = parent::get($timeout);
        $pdo->reset();

        return $pdo;
    }

    private function createConnection(): PDO
    {
        $pdo = $this->createPDO();

        foreach ($this->connectionBuilders as $connectionBuilder) {
            $pdo = $connectionBuilder->buildPDOConnection($pdo);
        }

        return $pdo;
    }

    private function createDSN(string $driver): string
    {
        return match ($driver) {
            'mariadb', 'mysql' => $this->config->hasUnixSocket()
                ? sprintf(
                    'mysql:unix_socket=%s;dbname=%s;charset=%s',
                    (string) $this->config->getUnixSocket(),
                    $this->config->getDbname(),
                    $this->config->getCharset()
                )
                : sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $this->config->getHost(),
                    $this->config->getPort(),
                    $this->config->getDbname(),
                    $this->config->getCharset()
                ),
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                (string) ($this->config->hasUnixSocket() ? $this->config->getUnixSocket() : $this->config->getHost()),
                $this->config->getPort(),
                $this->config->getDbname(),
            ),
            'oci' => sprintf(
                'oci:dbname=%s:%d/%s;charset=%s',
                (string) ($this->config->hasUnixSocket() ? $this->config->getUnixSocket() : $this->config->getHost()),
                $this->config->getPort(),
                $this->config->getDbname(),
                $this->config->getCharset()
            ),
        };
    }

    private function createPDO(): PDO
    {
        $driver = $this->config->getDriver();

        if ('sqlite' !== $driver) {
            return new PDO(
                $this->createDSN($driver),
                $this->config->getUsername(),
                $this->config->getPassword(),
                $this->config->getOptions()
            );
        }

        return new PDO(sprintf(
            'sqlite:%s',
            $this->config->getDbname()
        ));
    }
}
