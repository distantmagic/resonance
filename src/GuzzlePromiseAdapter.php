<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\SwooleFuture\PromiseState;
use Distantmagic\SwooleFuture\SwooleFuture;
use Distantmagic\SwooleFuture\SwooleFutureResult;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;

readonly class GuzzlePromiseAdapter implements PromiseInterface
{
    public static function fromExecutor(callable $executor): self
    {
        return new self(new SwooleFuture($executor));
    }

    public function __construct(private SwooleFuture $swooleFuture) {}

    public function cancel(): void
    {
        $this->reject(null);
    }

    public function getState(): string
    {
        return match ($this->swooleFuture->getState()) {
            PromiseState::Fulfilled => PromiseInterface::FULFILLED,
            PromiseState::Pending => PromiseInterface::PENDING,
            PromiseState::Rejected => PromiseInterface::REJECTED,
            PromiseState::Resolving => PromiseInterface::PENDING,
        };
    }

    public function otherwise(callable $onRejected): PromiseInterface
    {
        return new self(
            $this->swooleFuture->then(
                null,
                new SwooleFuture($onRejected),
            )
        );
    }

    public function reject($reason): void
    {
        $this->swooleFuture->resolve(new SwooleFutureResult(PromiseState::Rejected, $reason));
    }

    public function resolve($value): void
    {
        $this->swooleFuture->resolve($value);
    }

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface
    {
        return new self(
            $this->swooleFuture->then(
                $onFulfilled ? new SwooleFuture($onFulfilled) : null,
                $onRejected ? new SwooleFuture($onRejected) : null,
            )
        );
    }

    public function wait(bool $unwrap = true)
    {
        $this->swooleFuture->wait(1);

        if (!$unwrap) {
            return;
        }

        /**
         * @var mixed explicitly mixed for typechecks
         */
        $result = $this->swooleFuture->getResult();

        if (PromiseState::Rejected === $this->swooleFuture->getState()) {
            throw Create::exceptionFor($result);
        }

        return $result;
    }
}
