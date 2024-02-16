<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EnumConstraint::class)]
final class EnumConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new EnumConstraint(['foo', 'bar']);

        self::assertEquals([
            'type' => 'string',
            'enum' => ['foo', 'bar'],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new EnumConstraint(['foo', 'bar']);

        self::assertEquals([
            'type' => 'string',
            'enum' => ['foo', 'bar'],
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new EnumConstraint(['foo', 'bar']);

        self::assertEquals([
            'type' => ['null', 'string'],
            'enum' => ['foo', 'bar'],
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new EnumConstraint(['foo', 'bar']);

        self::assertTrue($constraint->validate('foo')->status->isValid());
        self::assertFalse($constraint->validate('booz')->status->isValid());
    }
}
