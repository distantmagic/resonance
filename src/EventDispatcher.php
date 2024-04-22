<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\SwooleFuture\SwooleFuture;
use Distantmagic\SwooleFuture\SwooleFutureResult;
use Psr\Log\LoggerInterface;

use function Distantmagic\Resonance\helpers\coroutineMustGo;
use function Swoole\Coroutine\batch;

#[Singleton(provides: EventDispatcherInterface::class)]
readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private EventListenerAggregate $eventListenerAggregate,
        private LoggerInterface $logger,
    ) {}

    public function collect(object $event): SwooleFutureResult
    {
        $future = new SwooleFuture(function (object $event): array {
            return $this->doDispatch($event);
        });

        return $future->resolve($event);
    }

    public function dispatch(object $event): void
    {
        coroutineMustGo(function () use ($event): void {
            $this->doDispatch($event);
        });
    }

    private function doDispatch(object $event): array
    {
        if (!($event instanceof LoggableInterface) || $event->shouldLog()) {
            $this->logger->debug(sprintf('event_dispatch(%s)', $event::class));
        }

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
