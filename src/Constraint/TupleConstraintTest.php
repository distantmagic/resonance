<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TupleConstraint::class)]
final class TupleConstraintTest extends TestCase
{
    public function test_data_is_mapped(): void
    {
        $constraint = new TupleConstraint(
            items: [
                new StringConstraint(),
                new NumberConstraint(),
            ]
        );

        $validatedResult = $constraint->validate(['hi', 5]);

        self::assertTrue($validatedResult->status->isValid());
        self::assertEquals(['hi', 5], $validatedResult->castedData);
    }

    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new TupleConstraint(
            items: [
                new StringConstraint(),
                new EnumConstraint(['foo']),
            ]
        );
        self::assertEquals([
            'type' => 'array',
            'items' => false,
            'prefixItems' => [
                [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                [
                    'type' => 'string',
                    'enum' => ['foo'],
                ],
            ],
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new TupleConstraint(
            items: [
                new StringConstraint(),
                new NumberConstraint(),
            ]
        );

        self::assertEquals([
            'type' => 'array',
            'items' => false,
            'prefixItems' => [
                [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                [
                    'type' => 'number',
                ],
            ],
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new TupleConstraint(
            items: [
                new StringConstraint(),
                new NumberConstraint(),
            ]
        );

        self::assertEquals([
            'type' => ['null', 'array'],
            'items' => false,
            'prefixItems' => [
                [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                [
                    'type' => 'number',
                ],
            ],
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates_fail(): void
    {
        $constraint = new TupleConstraint(
            items: [
                new StringConstraint(),
                new NumberConstraint(),
            ]
        );

        $validatedResult = $constraint->validate(['hi', 'ho']);

        self::assertFalse($validatedResult->status->isValid());
        self::assertEquals([
            '' => 'invalid_nested_constraint',
            '1' => 'invalid_data_type',
        ], $validatedResult->getErrors()->toArray());
    }

    public function test_validates_ok(): void
    {
        $constraint = new TupleConstraint(
            items: [
                new StringConstraint(),
                new NumberConstraint(),
            ]
        );

        self::assertTrue($constraint->validate(['hi', 5])->status->isValid());
        self::assertFalse($constraint->validate(['hi', 'ho'])->status->isValid());
    }

    public function test_validates_ok_default(): void
    {
        $constraint = new TupleConstraint(
            items: [
                new EnumConstraint(['hi', 'ho']),
                new AnyConstraint(),
                (new StringConstraint())->nullable(),
            ]
        );

        self::assertTrue($constraint->validate(['hi', 5])->status->isValid());
        self::assertTrue($constraint->validate(['hi', 5, null])->status->isValid());
    }
}
