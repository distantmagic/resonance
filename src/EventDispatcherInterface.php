<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface EventDispatcherInterface
{
    /**
     * Dispatch event and collect returned values.
     *
     * This value is not used internally in the framework, but it still might
     * be useful.
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function collect(EventInterface $event): SwooleFutureResult;

    /**
     * Dispatch event completely asynchronously and discard returned values.
     */
    public function dispatch(EventInterface $event): void;
}
