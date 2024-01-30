<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class EventListenerAggregate
{
    /**
     * @var Map<class-string,Set<EventListenerInterface>> $listeners
     */
    private Map $listeners;

    public function __construct()
    {
        $this->listeners = new Map();
    }

    /**
     * @param class-string $eventClass
     */
    public function addListener(string $eventClass, EventListenerInterface $eventListener): void
    {
        $this->createGetListenersSet($eventClass)->add($eventListener);
    }

    /**
     * @return Set<EventListenerInterface>
     */
    public function getListenersForEvent(object $event): Set
    {
        return $this->createGetListenersSet($event::class);
    }

    /**
     * @param class-string $eventClass
     */
    public function removeListener(string $eventClass, EventListenerInterface $eventListener): void
    {
        $this->createGetListenersSet($eventClass)->remove($eventListener);
    }

    /**
     * @param class-string $eventClass
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
