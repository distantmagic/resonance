<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use RuntimeException;
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
        /**
         * @var false|int $timerId
         */
        $timerId = Timer::after((int) ($timeout * 1000), $this->callback);

        if (!is_int($timerId)) {
            throw new RuntimeException('Unable to schedule a timer');
        }

        return new SwooleTimeoutScheduled($this->callback, $timerId);
    }
}
