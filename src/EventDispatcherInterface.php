<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\SwooleFuture\SwooleFutureResult;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * Dispatch event and collect returned values.
     *
     * This value is not used internally in the framework, but it still might
     * be useful.
     */
    public function collect(object $event): SwooleFutureResult;

    /**
     * Dispatch event completely asynchronously and discard returned values.
     */
    public function dispatch(object $event): void;
}
