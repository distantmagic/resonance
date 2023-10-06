<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Singleton;
use Resonance\Environment;
use Resonance\PHPProjectFiles;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Cache\FilesystemCache;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

/**
 * @template-extends SingletonProvider<TwigEnvironment>
 */
#[Singleton(provides: TwigEnvironment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigEnvironment
    {
        $cacheDirectory = DM_ROOT.'/cache/twig';

        if (DM_APP_ENV === Environment::Development) {
            $filesystem = new Filesystem();
            $filesystem->remove($cacheDirectory);
        }

        $loader = new FilesystemLoader(DM_APP_ROOT.'/views');
        $cache = new FilesystemCache($cacheDirectory, FilesystemCache::FORCE_BYTECODE_INVALIDATION);

        return new TwigEnvironment($loader, [
            'cache' => $cache,
        ]);
    }
}
