<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\GraphqlSwoolePromiseAdapter\SwoolePromiseAdapter;

trait TestsGraphQLQueriesTrait
{
    use TestsDependencyInectionContainerTrait;

    private static function assertValidQuery(string $query, array $expected): void
    {
        /**
         * @var null|array $result
         */
        $result = self::$container->call(static function (
            CrudActionGateAggregate $crudActionGateAggregate,
            GraphQLAdapter $graphQLAdapter,
        ) use ($query) {
            $swoolePromiseAdapter = new SwoolePromiseAdapter();

            return $graphQLAdapter
                ->query($swoolePromiseAdapter, $query)
                ->toArray()
            ;
        });

        self::assertIsArray($result);
        self::assertArrayNotHasKey('errors', $result);
        self::assertArrayHasKey('data', $result);
        self::assertEquals(
            [
                'data' => $expected,
            ],
            $result,
        );
    }
}
