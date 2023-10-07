<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
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
use function Swoole\Coroutine\run;

/**
 * @template-extends SingletonProvider<TwigEnvironment>
 */
#[Singleton(provides: TwigEnvironment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function __construct(private TwigBridgeExtension $twigBridgeExtension) {}

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

        /**
         * @var bool $coroutineResult
         */
        $coroutineResult = run(function () use ($environment) {
            $this->warmupCache($environment);
        });

        if (!$coroutineResult) {
            throw new RuntimeException('Coroutine event loop failed while warming up templates.');
        }

        return $environment;
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
