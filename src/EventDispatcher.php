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

    public function collect(EventInterface $event): SwooleFutureResult
    {
        $future = new SwooleFuture(function (EventInterface $event) {
            return $this->doDispatch($event);
        });

        return $future->resolve($event);
    }

    public function dispatch(EventInterface $event): void
    {
        $cid = go(function () use ($event) {
            $this->doDispatch($event);
        });

        if (!is_int($cid)) {
            throw new LogicException('Unable to start dispatcher coroutine');
        }
    }

    private function doDispatch(EventInterface $event): array
    {
        $listeners = $this->eventListenerAggregate->getListenersForEvent($event);

        if ($listeners->isEmpty()) {
            return [];
        }

        $batch = [];

        /**
         * @var EventListenerInterface $listener
         */
        foreach ($listeners as $listener) {
            array_push($batch, static function () use ($event, $listener): mixed {
                return $listener->handle($event);
            });
        }

        return batch($batch, DM_BATCH_PROMISE_TIMEOUT);
    }
}
