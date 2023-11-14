<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * @template-extends SingletonProvider<Psr17Factory>
 */
#[Singleton(provides: Psr17Factory::class)]
final readonly class NyholmPsr17FactoryProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Psr17Factory
    {
        return new Psr17Factory();
    }
}
