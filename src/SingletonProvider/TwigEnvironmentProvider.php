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
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Cache\FilesystemCache;
use Twig\Environment as TwigEnvironment;
use Twig\Error\Error;
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
        private Filesystem $filesystem,
        private LoggerInterface $logger,
        private TwigChainLoader $twigChainLoader,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigEnvironment
    {
        $twigEnvironment = new TwigEnvironment($this->twigChainLoader, [
            'cache' => $this->getCache(),
            'strict_variables' => false,
        ]);

        foreach ($this->collectExtensions($singletons) as $twigExtensionAttribute) {
            $twigEnvironment->addExtension($twigExtensionAttribute->singleton);
        }

        $this->warmupCache($twigEnvironment);

        return $twigEnvironment;
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

        $this->filesystem->remove($cacheDirectory);

        return new FilesystemCache($cacheDirectory, FilesystemCache::FORCE_BYTECODE_INVALIDATION);
    }

    private function warmupCache(TwigEnvironment $twigEnvironment): void
    {
        $viewsDirectory = DM_APP_ROOT.'/views';

        if (!is_dir($viewsDirectory)) {
            return;
        }

        $finder = new Finder();
        $found = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->in($viewsDirectory)
        ;

        foreach ($found as $file) {
            $relativePathname = $file->getRelativePathname();

            try {
                $twigEnvironment->load($relativePathname);
            } catch (Error $error) {
                $this->logger->warning(sprintf(
                    'twig_cache_warmup_error("%s", %d): %s',
                    $relativePathname,
                    $error->getTemplateLine(),
                    $error->getMessage()
                ));
            }
        }
    }
}
