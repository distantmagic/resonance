<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TEvent of object
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
    public function handle(object $event);
}
