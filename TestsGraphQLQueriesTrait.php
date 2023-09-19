<?php

declare(strict_types=1);

namespace Resonance;

use function Swoole\Coroutine\run;

trait TestsGraphQLQueriesTrait
{
    use TestsDependencyInectionContainerTrait;

    private static function assertValidQuery(string $query, array $expected): void
    {
        /**
         * @var null|array $result
         */
        $result = null;

        $coroutineResult = run(static function () use ($query, &$result) {
            $result = self::$container
                ->singletons
                ->get(GraphQLAdapter::class)
                ->query(
                    new SwoolePromiseAdapter(
                        new GraphQLDatabaseQueryAdapter(
                            new GatekeeperUserContext(
                                self::$container->singletons->get(SiteActionGateAggregate::class),
                                null,
                            ),
                        ),
                    ),
                    $query,
                )
                ->toArray()
            ;
        });

        self::assertTrue($coroutineResult);

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
