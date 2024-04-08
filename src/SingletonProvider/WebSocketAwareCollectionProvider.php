<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\WebSocketAwareCollection;
use Distantmagic\Resonance\WebSocketAwareInterface;

/**
 * @template-extends SingletonProvider<WebSocketAwareCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::WebSocketAware)]
#[Singleton(provides: WebSocketAwareCollection::class)]
final readonly class WebSocketAwareCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): WebSocketAwareCollection
    {
        $webSocketAwareCollection = new WebSocketAwareCollection();

        foreach ($singletons->values() as $singleton) {
            if ($singleton instanceof WebSocketAwareInterface) {
                $webSocketAwareCollection->webSocketAwares->put(
                    $singleton::class,
                    $singleton,
                );
            }
        }

        return $webSocketAwareCollection;
    }
}
