<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TEvent of EventInterface
 * @template TResult
 */
interface EventListenerInterface extends RegisterableInterface
{
    /**
     * I wish PHP had generics.
     *
     * @param TEvent $event
     *
     * @return TResult
     */
    public function handle(EventInterface $event);
}
