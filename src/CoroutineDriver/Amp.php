<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\CoroutineDriver;

use Closure;
use Distantmagic\Resonance\CoroutineDriver;
use Distantmagic\Resonance\CoroutineDriverException;
use Distantmagic\Resonance\CoroutineReference;
use Distantmagic\Resonance\CoroutineReferenceInterface;
use Swoole\Event;
use Swoole\Runtime;
use Throwable;

use function Amp\async;
use function Swoole\Coroutine\batch;
use function Swoole\Coroutine\run;

readonly class Amp extends CoroutineDriver
{
    public function batch(array $callbacks): array
    {
        return batch($callbacks, DM_BATCH_PROMISE_TIMEOUT);
    }

    public function go(callable $callback): CoroutineReferenceInterface
    {
        /**
         * @var Closure(...):mixed $closure
         */
        $closure = Closure::fromCallable($callback);

        async($closure);

        return new CoroutineReference();
    }

    public function init(): void
    {
        // Runtime::enableCoroutine(SWOOLE_HOOK_ALL);
    }

    /**
     * @template TReturn
     *
     * @param callable():TReturn $callback
     *
     * @return TReturn
     */
    public function run(callable $callback): mixed
    {
        return $callback();
        // /**
        //  * @var null|TReturn $ret
        //  */
        // $ret = null;

        // /**
        //  * Bringing this reference out of the coroutine event loops allows the
        //  * console component to catch that exception and format it.
        //  *
        //  * @var null|Throwable
        //  */
        // $exception = null;

        // /**
        //  * @var bool
        //  */
        // $coroutineResult = run(static function () use ($callback, &$exception, &$ret): void {
        //     try {
        //         $ret = $callback();
        //     } catch (Throwable $throwable) {
        //         $exception = $throwable;
        //     }
        // });

        // if (!$coroutineResult) {
        //     throw new CoroutineDriverException('Unable to start coroutine loop');
        // }

        // if ($exception) {
        //     throw $exception;
        // }

        // /**
        //  * @var TReturn might also be null, so no way to check for that
        //  */
        // return $ret;
    }

    public function wait(): void
    {
        // Event::wait();
    }
}
