<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigLoader;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigChainLoader;
use Distantmagic\Resonance\TwigOptionalLoaderInterface;
use Twig\Loader\LoaderInterface;

/**
 * @template-extends SingletonProvider<TwigChainLoader>
 */
#[RequiresSingletonCollection(SingletonCollection::TwigLoader)]
#[Singleton(provides: TwigChainLoader::class)]
readonly class TwigChainLoaderProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigChainLoader
    {
        $twigChainLoader = new TwigChainLoader();

        foreach ($this->collectTwigLoaders($singletons) as $twigLoaderAttribute) {
            if ($twigLoaderAttribute->singleton instanceof TwigOptionalLoaderInterface) {
                if (!$twigLoaderAttribute->singleton->shouldRegister()) {
                    continue;
                }

                $twigLoaderAttribute->singleton->beforeRegister();
            }

            $twigChainLoader->addLoader($twigLoaderAttribute->singleton);
        }

        return $twigChainLoader;
    }

    /**
     * @return iterable<SingletonAttribute<LoaderInterface,TwigLoader>>
     */
    private function collectTwigLoaders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            LoaderInterface::class,
            TwigLoader::class,
        );
    }
}
