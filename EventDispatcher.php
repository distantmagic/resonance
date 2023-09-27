<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;

use function Swoole\Coroutine\batch;

#[Singleton(provides: EventDispatcherInterface::class)]
readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private EventListenerAggregate $eventListenerAggregate,
    ) {}

    public function dispatch(EventInterface $event): SwooleFutureResult
    {
        $future = new SwooleFuture(function (EventInterface $event) {
            $listeners = $this->eventListenerAggregate->getListenersForEvent($event);

            if ($listeners->isEmpty()) {
                return;
            }

            $batch = [];

            /**
             * @var EventListenerInterface $listener
             */
            foreach ($listeners as $listener) {
                array_push($batch, static function () use (&$event, &$listener): mixed {
                    /**
                     * @var EventListenerInterface $listener
                     * @var EventInterface         $event
                     */
                    return $listener->handle($event);
                });
            }

            return batch($batch, DM_BATCH_PROMISE_TIMEOUT);
        });

        return $future->resolve($event);
    }
}
