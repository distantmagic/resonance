<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class BooleanConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new BooleanConstraint();

        self::assertEquals([
            'type' => 'boolean',
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new BooleanConstraint();

        self::assertEquals([
            'type' => 'boolean',
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new BooleanConstraint();

        self::assertEquals([
            'type' => ['null', 'boolean'],
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new BooleanConstraint();

        self::assertTrue($constraint->validate(false)->status->isValid());
        self::assertFalse($constraint->validate(5.5)->status->isValid());
    }
}
