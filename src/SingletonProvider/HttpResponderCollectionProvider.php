<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
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

        foreach ($singletons->values() as $singleton) {
            if ($singleton instanceof HttpResponderInterface) {
                $httpResponderCollection->httpResponders->put(
                    $singleton::class,
                    $singleton,
                );
            }
        }

        return $httpResponderCollection;
    }
}
