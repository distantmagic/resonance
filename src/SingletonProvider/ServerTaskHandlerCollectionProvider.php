<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\HandlesServerTask;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\ServerTaskHandlerCollection;
use Distantmagic\Resonance\ServerTaskHandlerInterface;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<ServerTaskHandlerCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::ServerTaskHandler)]
#[Singleton(provides: ServerTaskHandlerCollection::class)]
final readonly class ServerTaskHandlerCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ServerTaskHandlerCollection
    {
        $serverTaskHandlerCollection = new ServerTaskHandlerCollection();

        foreach ($this->collectTaskHandlers($singletons) as $serverTaskHandler) {
            $serverTaskHandlerCollection->addServerTaskHandler(
                $serverTaskHandler->attribute->className,
                $serverTaskHandler->singleton,
            );
        }

        return $serverTaskHandlerCollection;
    }

    /**
     * @return iterable<SingletonAttribute<ServerTaskHandlerInterface,HandlesServerTask>>
     */
    private function collectTaskHandlers(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            ServerTaskHandlerInterface::class,
            HandlesServerTask::class,
        );
    }
}
