<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @template-extends SingletonProvider<Filesystem>
 */
#[Singleton(provides: Filesystem::class)]
final readonly class FilesystemProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Filesystem
    {
        return new Filesystem();
    }
}
