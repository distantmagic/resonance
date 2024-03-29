<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use GraphQL\Error\DebugFlag;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use JsonSerializable;
use LogicException;

class GraphQLExecutionPromise implements JsonSerializable
{
    private ?ExecutionResult $executionResult = null;

    public function __construct(
        private readonly ApplicationConfiguration $applicationConfiguration,
        private readonly Promise $promise,
    ) {}

    public function getExecutionResult(): ExecutionResult
    {
        $this->promise->then(function (ExecutionResult $executionResult): void {
            $this->executionResult = $executionResult;
        });

        if (is_null($this->executionResult)) {
            throw new LogicException('Execution result was expected to be set.');
        }

        return $this->executionResult;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $result = $this->getExecutionResult();

        /**
         * @var array
         */
        return Environment::Production === $this->applicationConfiguration->environment
            ? $result->toArray()
            : $result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);
    }
}
