<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;

readonly class SwooleTimeout
{
    private Closure $callback;
    private SwooleTimeoutScheduler $swooleTimeoutScheduler;

    public function __construct(callable $callback)
    {
        $this->callback = Closure::fromCallable($callback);
        $this->swooleTimeoutScheduler = new SwooleTimeoutScheduler();
    }

    public function setTimeout(float $timeout): SwooleTimeoutScheduled
    {
        return new SwooleTimeoutScheduled(
            $this->callback,
            $this->swooleTimeoutScheduler->scheduleTimeout($timeout, $this->callback),
            $this->swooleTimeoutScheduler,
        );
    }
}
