<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use RuntimeException;
use Swoole\Coroutine;

readonly class SwooleTimeoutScheduled
{
    private Closure $callback;

    public function __construct(
        callable $callback,
        private int $coroutineId,
        private SwooleTimeoutScheduler $swooleTimeoutScheduler,
    ) {
        $this->callback = Closure::fromCallable($callback);
    }

    public function cancel(): bool
    {
        if (!Coroutine::exists($this->coroutineId)) {
            return true;
        }

        /**
         * @var bool
         */
        return Coroutine::cancel($this->coroutineId);
    }

    public function reschedule(float $timeout): self
    {
        if (!$this->cancel()) {
            throw new RuntimeException('Unable to cancel a coroutine.');
        }

        return new self(
            $this->callback,
            $this->swooleTimeoutScheduler->scheduleTimeout($timeout, $this->callback),
            $this->swooleTimeoutScheduler,
        );
    }
}
