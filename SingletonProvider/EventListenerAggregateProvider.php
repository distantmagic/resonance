<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\ListensTo;
use Resonance\Attribute\Singleton;
use Resonance\EventListenerAggregate;
use Resonance\EventListenerInterface;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<EventListenerAggregate>
 */
#[Singleton(
    provides: EventListenerAggregate::class,
    requiresCollection: SingletonCollection::EventListener,
)]
final readonly class EventListenerAggregateProvider extends SingletonProvider
{
    public function __construct(
    ) {}

    public function provide(SingletonContainer $singletons): EventListenerAggregate
    {
        $eventListenerAggregate = new EventListenerAggregate();

        foreach ($this->collectResponders($singletons) as $eventListenerAttribute) {
            $eventListenerAggregate->addListener(
                $eventListenerAttribute->attribute->eventClass,
                $eventListenerAttribute->singleton,
            );
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
