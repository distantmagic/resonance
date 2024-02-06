<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class ListConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new ListConstraint(
            valueConstraint: new StringConstraint()
        );
        self::assertEquals([
            'type' => 'array',
            'items' => [
                'type' => 'string',
                'minLength' => 1,
            ],
            'default' => [],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new ListConstraint(
            valueConstraint: new StringConstraint()
        );
        self::assertEquals([
            'type' => 'array',
            'items' => [
                'type' => 'string',
                'minLength' => 1,
            ],
            'default' => [],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new ListConstraint(
            valueConstraint: new StringConstraint()
        );
        self::assertEquals([
            'type' => ['null', 'array'],
            'items' => [
                'type' => 'string',
                'minLength' => 1,
            ],
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates_fail(): void
    {
        $constraint = new ListConstraint(
            valueConstraint: new StringConstraint()
        );

        $validatedResult = $constraint->validate([
            'hi',
            5,
        ]);
        self::assertFalse($validatedResult->status->isValid());
        self::assertEquals([
            '' => 'invalid_nested_constraint',
            '1' => 'invalid_data_type',
        ], $validatedResult->getErrors()->toArray());
    }

    public function test_validates_ok(): void
    {
        $constraint = new ListConstraint(
            valueConstraint: new StringConstraint()
        );

        $validatedResult = $constraint->validate([
            'hi',
            'foo',
        ]);

        self::assertTrue($validatedResult->status->isValid());
    }
}
