<?php

declare(strict_types=1);

namespace Resonance;

use LogicException;
use Resonance\Attribute\Singleton;

use function Swoole\Coroutine\batch;
use function Swoole\Coroutine\go;

#[Singleton(provides: EventDispatcherInterface::class)]
readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private EventListenerAggregate $eventListenerAggregate,
    ) {}

    public function dispatch(EventInterface $event): void
    {
        $listeners = $this->eventListenerAggregate->getListenersForEvent($event);

        if ($listeners->isEmpty()) {
            return;
        }

        $cid = go(static function () use (&$event, &$listeners) {
            $batch = [];

            foreach ($listeners as $listener) {
                $batch[] = static fn () => $listener->handle($event);
            }

            batch($batch);
        });

        if (!is_int($cid)) {
            throw new LogicException('Unable to start dispatcher coroutine');
        }
    }
}
