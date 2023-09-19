<?php

declare(strict_types=1);

namespace Resonance;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionParameter;

/**
 * @internal
 */
final class ConstructorParametersIteratorTestFixtureFoo {}

/**
 * @internal
 */
final class ConstructorParametersIteratorTestFixtureBar
{
    /**
     * This is an edge case that this test suite tests against
     *
     * @psalm-suppress MissingParamType
     * @psalm-suppress UnusedParam
     */
    public function __construct(
        ConstructorParametersIteratorTestFixtureFoo $foo,
        int $bar,
        $baz,
    ) {}
}

/**
 * @coversNothing
 *
 * @internal
 */
final class ConstructorParametersIteratorTest extends TestCase
{
    public function test_class_without_constructor(): void
    {
        $reflection = new ReflectionClass(ConstructorParametersIteratorTestFixtureFoo::class);
        $iter = new ConstructorParametersIterator($reflection);

        self::assertEquals([], iterator_to_array($iter));
    }

    public function test_constructor_arguments_are_extracted(): void
    {
        $reflection = new ReflectionClass(ConstructorParametersIteratorTestFixtureBar::class);
        $iter = new ConstructorParametersIterator($reflection);

        self::assertContainsOnlyInstancesOf(ReflectionParameter::class, $iter);
    }
}
