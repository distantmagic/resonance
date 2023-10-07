<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SingletonContainerTestFixtureFoo {}

/**
 * @coversNothing
 *
 * @internal
 */
final class SingletonContainerTest extends TestCase
{
    public function test_singletons_can_be_set(): void
    {
        $foo = new SingletonContainerTestFixtureFoo();

        $singletons = new SingletonContainer();
        $singletons->set(SingletonContainerTestFixtureFoo::class, $foo);

        self::assertTrue($singletons->has(SingletonContainerTestFixtureFoo::class));
        self::assertSame($foo, $singletons->get(SingletonContainerTestFixtureFoo::class));
    }
}
