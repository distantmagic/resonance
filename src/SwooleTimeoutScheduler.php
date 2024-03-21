<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Coroutine;

readonly class SwooleTimeoutScheduler
{
    public function scheduleTimeout(
        float $timeout,
        callable $callback,
    ): int {
        return SwooleCoroutineHelper::mustGo(static function () use ($callback, $timeout) {
            Coroutine::sleep($timeout);

            if (!Coroutine::isCanceled()) {
                $callback();
            }
        });
    }
}
