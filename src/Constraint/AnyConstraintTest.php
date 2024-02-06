<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversNothing
 *
 * @internal
 */
final class AnyConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new AnyConstraint();

        self::assertEquals(new stdClass(), $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new AnyConstraint();

        self::assertEquals(new stdClass(), $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new AnyConstraint();

        self::assertEquals(new stdClass(), $constraint->nullable()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new AnyConstraint();

        self::assertTrue($constraint->validate('foo')->status->isValid());
    }
}
