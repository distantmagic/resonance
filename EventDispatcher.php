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

            /**
             * @var EventListenerInterface $listener
             */
            foreach ($listeners as $listener) {
                array_push($batch, static function () use (&$event, &$listener): void {
                    /**
                     * @var EventListenerInterface $listener
                     * @var EventInterface         $event
                     */
                    $listener->handle($event);
                });
            }

            /**
             * Listeners have to return void, so there is no point to use their
             * return values here.
             *
             * @psalm-suppress UnusedFunctionCall
             */
            batch($batch, DM_BATCH_PROMISE_TIMEOUT);
        });

        if (!is_int($cid)) {
            throw new LogicException('Unable to start dispatcher coroutine');
        }
    }
}
