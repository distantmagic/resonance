<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(NumberConstraint::class)]
final class NumberConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new NumberConstraint();

        self::assertEquals([
            'type' => 'number',
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new NumberConstraint();

        self::assertEquals([
            'type' => 'number',
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new NumberConstraint();

        self::assertEquals([
            'type' => ['null', 'number'],
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new NumberConstraint();

        self::assertTrue($constraint->validate(5)->status->isValid());
        self::assertTrue($constraint->validate(5.5)->status->isValid());
    }
}
