<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use PDO;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

/**
 * @template-extends SingletonProvider<PDOPool>
 */
#[Singleton(provides: PDOPool::class)]
final readonly class SwoolePDOPoolProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): PDOPool
    {
        return new PDOPool($this->configFromGlobals());
    }

    public function shouldRegister(): bool
    {
        return !empty(DM_DB_HOST);
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
