<?php

declare(strict_types=1);

namespace Resonance;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @internal
 */
final class DependencyInjectionContainerTestFixtureFoo {}

/**
 * @internal
 */
final class DependencyInjectionContainerTestFixtureBar
{
    public function __construct(public DependencyInjectionContainerTestFixtureFoo $foo) {}
}

/**
 * @coversNothing
 *
 * @internal
 */
final class DependencyInjectionContainerTest extends MockeryTestCase
{
    public function test_singleton_is_built(): void
    {
        $container = new DependencyInjectionContainer();
        $foo = new DependencyInjectionContainerTestFixtureFoo();

        $container
            ->singletons
            ->set(DependencyInjectionContainerTestFixtureFoo::class, $foo)
        ;

        $built = $container->make(DependencyInjectionContainerTestFixtureBar::class);

        self::assertInstanceOf(DependencyInjectionContainerTestFixtureBar::class, $built);
        self::assertSame($foo, $built->foo);
    }
}
