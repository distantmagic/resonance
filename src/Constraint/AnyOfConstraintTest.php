<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class AnyOfConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new AnyOfConstraint(
            anyOf: [
                new StringConstraint(),
                new IntegerConstraint(),
            ]
        );

        self::assertEquals([
            'anyOf' => [
                [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                [
                    'type' => 'integer',
                ],
            ],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new AnyOfConstraint(
            anyOf: [
                new StringConstraint(),
                new IntegerConstraint(),
            ]
        );

        self::assertEquals([
            'anyOf' => [
                [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                [
                    'type' => 'integer',
                ],
            ],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new AnyOfConstraint(
            anyOf: [
                new StringConstraint(),
                new IntegerConstraint(),
            ]
        );

        self::assertTrue($constraint->validate('hi')->status->isValid());
        self::assertTrue($constraint->validate(5)->status->isValid());
        self::assertFalse($constraint->validate(false)->status->isValid());
    }
}
