<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Hyperf\Coroutine\Channel\Pool;

/**
 * @template-extends SingletonProvider<Pool>
 */
#[Singleton(provides: Pool::class)]
final readonly class HyperfChannelPool extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Pool
    {
        return Pool::getInstance();
    }
}
