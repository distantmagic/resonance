<?php

declare(strict_types=1);

namespace Resonance;

use Closure;
use Ds\Set;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use LogicException;
use Swoole\Coroutine\WaitGroup;
use Throwable;

use function Swoole\Coroutine\go;

final class SwooleFuture
{
    public readonly Closure $executor;

    /**
     * @var Set<WaitGroup> $awaitingThenables
     */
    private Set $awaitingThenables;

    private mixed $result = null;
    private PromiseState $state = PromiseState::Pending;

    // public static function create(callable $executor): self
    // {
    //     return new self($executor);
    // }

    // /**
    //  * @param callable(mixed,mixed,mixed,ResolveInfo):mixed $callback
    //  *
    //  * @psalm-suppress MixedFunctionCall callback can return anything
    //  */
    // public static function resolver(callable $callback): callable
    // {
    //     return static function (mixed $rootValue, array $args, mixed $context, ResolveInfo $resolveInfo) use (&$callback): self {
    //         return new self(static function () use (&$args, &$callback, &$context, &$resolveInfo, &$rootValue): mixed {
    //             return $callback($rootValue, $args, $context, $resolveInfo);
    //         });
    //     };
    // }

    public function __construct(callable $executor)
    {
        $this->awaitingThenables = new Set();
        $this->executor = $executor instanceof Closure
            ? $executor
            : Closure::fromCallable($executor);
    }

    public function resolve(mixed $value): SwooleFutureResult
    {
        if (PromiseState::Resolving === $this->state) {
            throw new LogicException('SwooleFuture is currently resolving');
        }

        if (PromiseState::Pending !== $this->state) {
            throw new LogicException('SwooleFuture is already resolved');
        }

        $this->state = PromiseState::Resolving;

        $waitGroup = new WaitGroup();

        $cid = go(function () use (&$value, $waitGroup) {
            $waitGroup->add();

            try {
                $this->result = ($this->executor)($value);
                $this->state = PromiseState::Fulfilled;
            } catch (Throwable $throwable) {
                $this->result = $throwable;
                $this->state = PromiseState::Rejected;
            } finally {
                $waitGroup->done();
            }
        });

        if (!is_int($cid)) {
            throw new LogicException('Unable to start an executor Coroutine');
        }

        if (!$waitGroup->wait(DM_GRAPHQL_PROMISE_TIMEOUT)) {
            return $this->reportWaitGroupFailure();
        }

        if (!$this->state->isSettled()) {
            throw new LogicException('Unexpected non-settled state');
        }

        $this->unwrapResult();

        /**
         * Both state and result are settled and final
         */
        foreach ($this->awaitingThenables as $awaitingThenable) {
            $awaitingThenable->done();
        }

        $this->awaitingThenables->clear();

        return new SwooleFutureResult($this->state, $this->result);
    }

    public function then(?self $onFulfilled, ?self $onRejected): self
    {
        if (is_null($onFulfilled) && is_null($onRejected)) {
            throw new LogicException('Must provide at least one chain callback');
        }

        $waitGroup = $this->state->isSettled() ? null : new WaitGroup();
        $waitGroup?->add();

        if ($waitGroup) {
            $this->awaitingThenables->add($waitGroup);
        }

        return new self(function (mixed $value) use ($onFulfilled, $onRejected, $waitGroup) {
            if (PromiseState::Pending === $this->state) {
                // This resolve's result cannot be used here as the promise can
                // be resolving at the moment and this code branch might not be
                // visited.
                $this->resolve($value);
            }

            if (
                !$this->state->isSettled()
                && !is_null($waitGroup)
                && !$waitGroup->wait(DM_GRAPHQL_PROMISE_TIMEOUT)
            ) {
                return $this->reportWaitGroupFailure();
            }

            $this->awaitingThenables->clear();

            if (!$this->state->isSettled()) {
                throw new LogicException('Cannot chain non-settled Future: '.$this->state->name);
            }

            if (PromiseState::Fulfilled === $this->state && $onFulfilled) {
                return $onFulfilled->resolve($this->result);
            }

            if (PromiseState::Rejected === $this->state && $onRejected) {
                return $onRejected->resolve($this->result);
            }

            return new SwooleFutureResult($this->state, $this->result);
        });
    }

    private function reportWaitGroupFailure(): SwooleFutureResult
    {
        return new SwooleFutureResult(
            PromiseState::Rejected,
            new LogicException('WaitGroup failed while resolving promise (likely due to a timeout)'),
        );
    }

    private function unwrapResult(int $nestingLimit = 3): void
    {
        if ($nestingLimit < 0) {
            throw new LogicException('SwooleFuture\'s result is to deeply nested');
        }

        /**
         * Unwrap SwooleFutureResult (from `then`)
         */
        if ($this->result instanceof SwooleFutureResult) {
            $this->state = $this->result->state;
            $this->result = $this->result->result;

            $this->unwrapResult($nestingLimit - 1);
        } elseif ($this->result instanceof Promise) {
            $adoptedPromise = $this->result->adoptedPromise;

            if ($adoptedPromise instanceof SwooleFutureResult) {
                /**
                 * @var SwooleFutureResult $adoptedPromise
                 */
                $this->state = $adoptedPromise->state;
                $this->result = $adoptedPromise->result;

                $this->unwrapResult($nestingLimit - 1);
            } else {
                throw new LogicException('SwooleFuture is not fully resolved.');
            }
        }
    }
}
