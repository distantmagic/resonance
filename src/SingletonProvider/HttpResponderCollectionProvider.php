<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpResponder)]
#[Singleton(provides: HttpResponderCollection::class)]
final readonly class HttpResponderCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpResponderCollection
    {
        $httpResponderCollection = new HttpResponderCollection();

        foreach ($this->collectResponders($singletons) as $httpResponderAttribute) {
            $httpResponderCollection->httpResponders->put(
                // $httpResponderAttribute->attribute->routeSymbol,
                $httpResponderAttribute->singleton::class,
                $httpResponderAttribute->singleton,
            );
        }

        return $httpResponderCollection;
    }

    /**
     * @return iterable<SingletonAttribute<HttpResponderInterface,RespondsToHttp>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpResponderInterface::class,
            RespondsToHttp::class,
        );
    }
}
