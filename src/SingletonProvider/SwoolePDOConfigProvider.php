<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use PDO;
use Swoole\Database\PDOConfig;

/**
 * @template-extends SingletonProvider<PDOConfig>
 */
#[Singleton(provides: PDOConfig::class)]
final readonly class SwoolePDOConfigProvider extends SingletonProvider
{
    public function __construct(private DatabaseConfiguration $databaseConfiguration) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): PDOConfig
    {
        $pdoConfig = new PDOConfig();

        return $pdoConfig
            ->withHost($this->databaseConfiguration->host)
            ->withPort($this->databaseConfiguration->port)
            ->withDbName($this->databaseConfiguration->database)
            ->withUsername($this->databaseConfiguration->username)
            ->withPassword($this->databaseConfiguration->password)
            ->withOptions([
                PDO::ERRMODE_EXCEPTION,
            ])
        ;
    }
}
