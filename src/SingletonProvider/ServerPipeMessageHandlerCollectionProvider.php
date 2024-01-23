<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\HandlesServerPipeMessage;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\ServerPipeMessageHandlerCollection;
use Distantmagic\Resonance\ServerPipeMessageHandlerInterface;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<ServerPipeMessageHandlerCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::ServerPipeMessageHandler)]
#[Singleton(provides: ServerPipeMessageHandlerCollection::class)]
final readonly class ServerPipeMessageHandlerCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ServerPipeMessageHandlerCollection
    {
        $serverPipeMessageHandlerCollection = new ServerPipeMessageHandlerCollection();

        foreach ($this->collectPipeMessageHandlers($singletons) as $serverPipeMessageHandler) {
            $serverPipeMessageHandlerCollection->addServerPipeMessageHandler(
                $serverPipeMessageHandler->attribute->className,
                $serverPipeMessageHandler->singleton,
            );
        }

        return $serverPipeMessageHandlerCollection;
    }

    /**
     * @return iterable<SingletonAttribute<ServerPipeMessageHandlerInterface,HandlesServerPipeMessage>>
     */
    private function collectPipeMessageHandlers(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            ServerPipeMessageHandlerInterface::class,
            HandlesServerPipeMessage::class,
        );
    }
}
