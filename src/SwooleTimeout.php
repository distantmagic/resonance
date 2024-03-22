<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Swoole\Timer;

readonly class SwooleTimeout
{
    private Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = Closure::fromCallable($callback);
    }

    public function setTimeout(float $timeout): SwooleTimeoutScheduled
    {
        return new SwooleTimeoutScheduled(
            $this->callback,
            Timer::after((int) ($timeout * 1000), $this->callback),
        );
    }
}
