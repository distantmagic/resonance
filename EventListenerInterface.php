<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TEvent of EventInterface
 */
interface EventListenerInterface
{
    /**
     * I wish PHP had generics.
     *
     * @param TEvent $event
     */
    public function handle(EventInterface $event): void;

    public function shouldRegister(): bool;
}
