<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use RuntimeException;
use Throwable;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

final readonly class SwooleCoroutineHelper
{
    /**
     * @param callable() $callback
     */
    public static function mustGo(callable $callback): int
    {
        /**
         * @var false|int $cid
         */
        $cid = go($callback);

        if (!is_int($cid)) {
            throw new RuntimeException('Unable to start a coroutine');
        }

        return $cid;
    }

    /**
     * @template TReturn
     *
     * @param callable():TReturn $callback
     *
     * @return TReturn
     */
    public static function mustRun(callable $callback): mixed
    {
        /**
         * @var null|TReturn $ret
         */
        $ret = null;

        /**
         * Bringing this reference out of the coroutine event loops allows the
         * console component to catch that exception and format it.
         *
         * @var null|Throwable
         */
        $exception = null;

        /**
         * @var bool
         */
        $coroutineResult = run(static function () use ($callback, &$exception, &$ret): void {
            try {
                $ret = $callback();
            } catch (Throwable $throwable) {
                $exception = $throwable;
            }
        });

        if (!$coroutineResult) {
            throw new RuntimeException('Unable to start coroutine loop');
        }

        if ($exception) {
            throw $exception;
        }

        /**
         * @var TReturn might also be null, so no way to check for that
         */
        return $ret;
    }
}
