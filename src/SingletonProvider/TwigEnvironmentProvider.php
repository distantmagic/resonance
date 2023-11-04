<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigLoader;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigBridgeExtension;
use Distantmagic\Resonance\TwigChainLoader;
use Distantmagic\Resonance\SingletonAttribute;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Cache\FilesystemCache;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

/**
 * @template-extends SingletonProvider<TwigEnvironment>
 */
#[Singleton(provides: TwigEnvironment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private TwigBridgeExtension $twigBridgeExtension,
        private TwigChainLoader $twigChainLoader,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigEnvironment
    {
        $environment = new TwigEnvironment($this->twigChainLoader, [
            'cache' => $this->getCache(),
            'strict_variables' => false,
        ]);

        $environment->addExtension($this->twigBridgeExtension);

        return $environment;
    }

    private function getCache(): false|FilesystemCache
    {
        if (Environment::Development === $this->applicationConfiguration->environment) {
            return false;
        }

        $cacheDirectory = DM_ROOT.'/cache/twig';

        $filesystem = new Filesystem();
        $filesystem->remove($cacheDirectory);

        return new FilesystemCache($cacheDirectory, FilesystemCache::FORCE_BYTECODE_INVALIDATION);
    }
}
