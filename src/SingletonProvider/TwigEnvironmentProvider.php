<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigBridgeExtension;
use Distantmagic\Resonance\TwigCacheRuntimeLoader;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Cache\FilesystemCache;
use Twig\Environment as TwigEnvironment;
use Twig\Extra\Cache\CacheExtension;
use Twig\Loader\FilesystemLoader;

/**
 * @template-extends SingletonProvider<TwigEnvironment>
 */
#[Singleton(provides: TwigEnvironment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function __construct(
        private TwigBridgeExtension $twigBridgeExtension,
        private TwigCacheRuntimeLoader $twigCacheRuntimeLoader,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigEnvironment
    {
        $cacheDirectory = DM_ROOT.'/cache/twig';

        $filesystem = new Filesystem();
        $filesystem->remove($cacheDirectory);

        $loader = new FilesystemLoader(DM_APP_ROOT.'/views');
        $cache = new FilesystemCache($cacheDirectory, FilesystemCache::FORCE_BYTECODE_INVALIDATION);

        $environment = new TwigEnvironment($loader, [
            'cache' => $cache,
            'strict_variables' => false,
        ]);

        $environment->addExtension($this->twigBridgeExtension);

        $environment->addRuntimeLoader($this->twigCacheRuntimeLoader);

        return $environment;
    }
}
