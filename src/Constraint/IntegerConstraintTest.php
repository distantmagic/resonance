<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class IntegerConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new IntegerConstraint();

        self::assertEquals([
            'type' => 'integer',
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new IntegerConstraint();

        self::assertEquals([
            'type' => 'integer',
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new IntegerConstraint();

        self::assertEquals([
            'type' => ['null', 'integer'],
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new IntegerConstraint();

        self::assertTrue($constraint->validate(5)->status->isValid());
        self::assertFalse($constraint->validate(5.5)->status->isValid());
    }
}
