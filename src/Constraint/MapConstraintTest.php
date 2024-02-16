<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MapConstraint::class)]
final class MapConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new MapConstraint(
            valueConstraint: new StringConstraint()
        );
        self::assertEquals([
            'type' => 'object',
            'additionalProperties' => [
                'type' => 'string',
                'minLength' => 1,
            ],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new MapConstraint(
            valueConstraint: new StringConstraint()
        );
        self::assertEquals([
            'type' => 'object',
            'additionalProperties' => [
                'type' => 'string',
                'minLength' => 1,
            ],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new MapConstraint(
            valueConstraint: new StringConstraint()
        );
        self::assertEquals([
            'type' => ['null', 'object'],
            'additionalProperties' => [
                'type' => 'string',
                'minLength' => 1,
            ],
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates_fail(): void
    {
        $constraint = new MapConstraint(
            valueConstraint: new StringConstraint()
        );

        $validatedResult = $constraint->validate([
            'aaa' => 'hi',
            'bbb' => 5,
        ]);
        self::assertFalse($validatedResult->status->isValid());
        self::assertEquals([
            '' => 'invalid_nested_constraint',
            'bbb' => 'invalid_data_type',
        ], $validatedResult->getErrors()->toArray());
    }

    public function test_validates_ok(): void
    {
        $constraint = new MapConstraint(
            valueConstraint: new StringConstraint()
        );

        $validatedResult = $constraint->validate([
            'aaa' => 'hi',
            'bbb' => 'foo',
        ]);

        self::assertTrue($validatedResult->status->isValid());
    }
}
