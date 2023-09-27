<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;

#[Singleton(provides: EventDispatcherInterface::class)]
readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private EventListenerAggregate $eventListenerAggregate,
    ) {}

    public function dispatch(EventInterface $event): void
    {
        foreach ($this->eventListenerAggregate->getListenersForEvent($event) as $listener) {
            $listener->handle($event);
        }
    }
}
