<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigBridgeExtension;
use LogicException;
use RuntimeException;
use Swoole\Coroutine\WaitGroup;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Cache\FilesystemCache;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

use function Swoole\Coroutine\go;

/**
 * @template-extends SingletonProvider<TwigEnvironment>
 */
#[Singleton(provides: TwigEnvironment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private TwigBridgeExtension $twigBridgeExtension,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigEnvironment
    {
        $loader = new FilesystemLoader(DM_APP_ROOT.'/views');

        $environment = new TwigEnvironment($loader, [
            'cache' => $this->getCache(),
            'strict_variables' => false,
        ]);

        $environment->addExtension($this->twigBridgeExtension);

        $this->warmupCache($environment);

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

    private function warmupCache(TwigEnvironment $environment): void
    {
        $finder = new Finder();
        $found = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->name('*.twig')
            ->in(DM_APP_ROOT.'/views')
        ;

        $waitGroup = new WaitGroup();

        foreach ($found as $template) {
            $waitGroup->add();

            $cid = go(static function () use ($environment, $template, $waitGroup) {
                try {
                    $environment->load($template->getRelativePathname());
                } finally {
                    $waitGroup->done();
                }
            });

            if (!is_int($cid)) {
                throw new LogicException('Unable to start template loader coroutine');
            }
        }

        // Give it 100ms / template
        if (!$waitGroup->wait($waitGroup->count() * 0.1)) {
            throw new RuntimeException('Cache warmup timed out.');
        }
    }
}
