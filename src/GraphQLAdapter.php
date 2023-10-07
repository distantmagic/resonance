<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use GraphQL\GraphQL as GraphQLFacade;
use GraphQL\Type\Schema;
use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class GraphQLAdapter
{
    public function __construct(private Schema $schema) {}

    /**
     * @param null|array<string,mixed> $variableValues
     */
    public function query(
        SwoolePromiseAdapter $swoolePromiseAdapter,
        string $query,
        mixed $rootValue = null,
        mixed $context = null,
        ?array $variableValues = null,
    ): GraphQLExecutionPromise {
        $promise = GraphQLFacade::promiseToExecute(
            $swoolePromiseAdapter,
            $this->schema,
            $query,
            $rootValue,
            $context,
            $variableValues,
        );

        return new GraphQLExecutionPromise($promise);
    }
}
