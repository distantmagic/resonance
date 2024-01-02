<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TEvent of EventInterface
 * @template TResult
 *
 * @template-implements EventListenerInterface<TEvent, TResult>
 */
abstract readonly class EventListener implements EventListenerInterface
{
    public function shouldRegister(): bool
    {
        return true;
    }
}
