<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use PDO;
use Resonance\Attribute\Singleton;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @template-extends SingletonProvider<PDOPool>
 */
#[Singleton(provides: PDOPool::class)]
final readonly class SwoolePDOPoolProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, ?ConsoleOutputInterface $output = null): PDOPool
    {
        return new PDOPool($this->configFromGlobals());
    }

    private function configFromGlobals(): PDOConfig
    {
        $pdoConfig = new PDOConfig();

        return $pdoConfig
            ->withHost(DM_DB_HOST)
            ->withPort(DM_DB_PORT)
            ->withDbName(DM_DB_DATABASE)
            ->withUsername(DM_DB_USERNAME)
            ->withPassword(DM_DB_PASSWORD)
            ->withOptions([
                PDO::ERRMODE_EXCEPTION,
            ])
        ;
    }
}
