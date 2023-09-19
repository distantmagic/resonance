<?php

declare(strict_types=1);

namespace Resonance;

use GraphQL\Error\DebugFlag;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use JsonSerializable;
use LogicException;

class GraphQLExecutionPromise implements JsonSerializable
{
    private ?ExecutionResult $executionResult = null;

    public function __construct(private Promise $promise) {}

    public function getExecutionResult(): ExecutionResult
    {
        $this->promise->then(function (ExecutionResult $executionResult) {
            $this->executionResult = $executionResult;
        });

        if (is_null($this->executionResult)) {
            throw new LogicException('Execution result was expected to be set.');
        }

        return $this->executionResult;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     *
     * @todo revisit after factoring
     */
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
        return DM_APP_ENV === Environment::Production
            ? $result->toArray()
            : $result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);
    }
}
