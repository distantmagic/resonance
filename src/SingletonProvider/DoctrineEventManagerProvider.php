<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ListensToDoctrineEvents;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;

/**
 * @template-extends SingletonProvider<EventManager>
 */
#[RequiresSingletonCollection(SingletonCollection::DoctrineEventListener)]
#[Singleton(provides: EventManager::class)]
final readonly class DoctrineEventManagerProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): EventManager
    {
        $eventManger = new EventManager();

        foreach ($this->collectEventSubscribers($singletons) as $subscriberAttribute) {
            $eventManger->addEventSubscriber($subscriberAttribute->singleton);
        }

        return $eventManger;
    }

    /**
     * @return iterable<SingletonAttribute<EventSubscriber,ListensToDoctrineEvents>>
     */
    private function collectEventSubscribers(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            EventSubscriber::class,
            ListensToDoctrineEvents::class,
        );
    }
}
