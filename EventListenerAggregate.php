<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Set;

readonly class EventListenerAggregate
{
    /**
     * @var Map<class-string<EventInterface>,Set<EventListenerInterface>> $listeners
     */
    private Map $listeners;

    public function __construct()
    {
        $this->listeners = new Map();
    }

    /**
     * @param class-string<EventInterface> $eventClass
     */
    public function addListener(string $eventClass, EventListenerInterface $eventListener): void
    {
        if (!$eventListener->shouldRegister()) {
            return;
        }

        $this->createGetListenersSet($eventClass)->add($eventListener);
    }

    /**
     * @return Set<EventListenerInterface>
     */
    public function getListenersForEvent(EventInterface $event): Set
    {
        return $this->createGetListenersSet($event::class);
    }

    /**
     * @param class-string<EventInterface> $eventClass
     *
     * @return Set<EventListenerInterface>
     */
    private function createGetListenersSet(string $eventClass): Set
    {
        if (!$this->listeners->hasKey($eventClass)) {
            $this->listeners->put($eventClass, new Set());
        }

        return $this->listeners->get($eventClass);
    }
}