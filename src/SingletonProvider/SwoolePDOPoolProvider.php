<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

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
    public function __construct(private PDOConfig $pdoConfig) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): PDOPool
    {
        return new PDOPool($this->pdoConfig);
    }
}
