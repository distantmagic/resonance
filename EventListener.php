<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TEvent of EventInterface
 *
 * @template-implements EventListenerInterface<TEvent>
 */
abstract readonly class EventListener implements EventListenerInterface
{
    public function shouldRegister(): bool
    {
        return true;
    }
}
