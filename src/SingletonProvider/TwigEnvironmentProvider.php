<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigExtension;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigChainLoader;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Cache\FilesystemCache;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\ExtensionInterface;

/**
 * @template-extends SingletonProvider<TwigEnvironment>
 */
#[RequiresSingletonCollection(SingletonCollection::TwigExtension)]
#[Singleton(provides: TwigEnvironment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private TwigChainLoader $twigChainLoader,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigEnvironment
    {
        return new TwigEnvironment($this->twigChainLoader, [
            'cache' => $this->getCache(),
            'strict_variables' => false,
        ]);
    }

    /**
     * @return iterable<SingletonAttribute<ExtensionInterface,TwigExtension>>
     */
    private function collectExtensions(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            ExtensionInterface::class,
            TwigExtension::class,
        );
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
