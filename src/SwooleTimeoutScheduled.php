<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use RuntimeException;
use Swoole\Timer;

readonly class SwooleTimeoutScheduled
{
    private Closure $callback;

    public function __construct(
        callable $callback,
        private int $timeoutId,
    ) {
        $this->callback = Closure::fromCallable($callback);
    }

    public function cancel(): bool
    {
        /**
         * @var bool
         */
        return Timer::clear($this->timeoutId);
    }

    public function reschedule(float $timeout): self
    {
        if (!$this->cancel()) {
            throw new RuntimeException('Unable to cancel a coroutine.');
        }

        /**
         * @var false|int $timerId
         */
        $timerId = Timer::after((int) ($timeout * 1000), $this->callback);

        if (!is_int($timerId)) {
            throw new RuntimeException('Unable to schedule a timer');
        }

        return new self($this->callback, $timerId);
    }
}
