<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigFunctionCollection;
use Distantmagic\Resonance\TwigFunctionInterface;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::TwigFunction)]
#[Singleton(provides: TwigFunctionCollection::class)]
final readonly class TwigFunctionCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigFunctionCollection
    {
        $twigFunctionCollection = new TwigFunctionCollection();

        foreach ($this->collectTwigFunctions($singletons) as $httpResponderAttribute) {
            $twigFunctionCollection->twigFunctions->add($httpResponderAttribute->singleton);
        }

        return $twigFunctionCollection;
    }

    /**
     * @return iterable<SingletonAttribute<TwigFunctionInterface,TwigFunction>>
     */
    private function collectTwigFunctions(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            TwigFunctionInterface::class,
            TwigFunction::class,
        );
    }
}
