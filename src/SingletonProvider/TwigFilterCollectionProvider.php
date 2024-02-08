<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFilter;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigFilterCollection;
use Distantmagic\Resonance\TwigFilterInterface;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::TwigFilter)]
#[Singleton(provides: TwigFilterCollection::class)]
final readonly class TwigFilterCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigFilterCollection
    {
        $twigFilterCollection = new TwigFilterCollection();

        foreach ($this->collectTwigFilters($singletons) as $httpResponderAttribute) {
            $twigFilterCollection->twigFilters->add($httpResponderAttribute->singleton);
        }

        return $twigFilterCollection;
    }

    /**
     * @return iterable<SingletonAttribute<TwigFilterInterface,TwigFilter>>
     */
    private function collectTwigFilters(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            TwigFilterInterface::class,
            TwigFilter::class,
        );
    }
}
