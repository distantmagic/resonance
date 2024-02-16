<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ConstConstraint::class)]
final class ConstConstraintTest extends TestCase
{
    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new ConstConstraint(2);

        self::assertEquals([
            'const' => 2,
        ], $constraint->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new ConstConstraint(4);

        self::assertTrue($constraint->validate(4)->status->isValid());
        self::assertFalse($constraint->validate(4.5)->status->isValid());
    }
}
