<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\EventListenerAggregate;
use Distantmagic\Resonance\EventListenerInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<EventListenerAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::EventListener)]
#[Singleton(provides: EventListenerAggregate::class)]
final readonly class EventListenerAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): EventListenerAggregate
    {
        $eventListenerAggregate = new EventListenerAggregate();

        foreach ($this->collectResponders($singletons) as $eventListenerAttribute) {
            if ($eventListenerAttribute->singleton->shouldRegister()) {
                $eventListenerAggregate->addListener(
                    $eventListenerAttribute->attribute->eventClass,
                    $eventListenerAttribute->singleton,
                );
            }
        }

        return $eventListenerAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<EventListenerInterface,ListensTo>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            EventListenerInterface::class,
            ListensTo::class,
        );
    }
}
