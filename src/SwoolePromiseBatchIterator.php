<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use GraphQL\Executor\Promise\Promise;
use IteratorAggregate;
use LogicException;

/**
 * @template-implements IteratorAggregate<callable>
 */
readonly class SwoolePromiseBatchIterator implements IteratorAggregate
{
    /**
     * @param iterable<mixed> $promisesOrValues
     */
    public function __construct(private iterable $promisesOrValues) {}

    /**
     * @return Generator<callable>
     */
    public function getIterator(): Generator
    {
        /**
         * @var mixed $promiseOrValue explicitly mixed for typechecks
         */
        foreach ($this->promisesOrValues as $promiseOrValue) {
            yield $this->promiseOrValueToCallback($promiseOrValue);
        }
    }

    private function promiseOrValueToCallback(mixed $promiseOrValue): callable
    {
        return function () use ($promiseOrValue): mixed {
            $swoolePromiseResult = $this->promiseOrValueToResult($promiseOrValue);

            if (!$swoolePromiseResult->state->isSettled()) {
                throw new LogicException('Unexpected promise.all non-settled state.');
            }

            return $swoolePromiseResult->result;
        };
    }

    private function promiseOrValueToResult(mixed $promiseOrValue): SwooleFutureResult
    {
        if (!($promiseOrValue instanceof Promise)) {
            return new SwooleFutureResult(PromiseState::Fulfilled, $promiseOrValue);
        }

        $adoptedPromise = $promiseOrValue->adoptedPromise;

        if ($adoptedPromise instanceof SwooleFutureResult) {
            return $adoptedPromise;
        }

        if ($adoptedPromise instanceof SwooleFuture) {
            /**
             * @var SwooleFuture $adoptedPromise
             */
            return $adoptedPromise->resolve(null);
        }

        throw new LogicException('Unexpected promise.all batch value');
    }
}
