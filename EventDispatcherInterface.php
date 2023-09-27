<?php

declare(strict_types=1);

namespace Resonance;

interface EventDispatcherInterface
{
    /**
     * This value is not used internally in the framework, but it still might
     * be useful.
     *
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function dispatch(EventInterface $event): SwooleFutureResult;
}
