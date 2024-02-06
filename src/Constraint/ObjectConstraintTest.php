<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class ObjectConstraintTest extends TestCase
{
    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => new StringConstraint(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'aaa' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'bbb' => [
                    'type' => 'string',
                    'enum' => ['foo'],
                ],
            ],
            'required' => ['aaa', 'bbb'],
            'additionalProperties' => false,
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => new StringConstraint(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'aaa' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'bbb' => [
                    'type' => 'string',
                    'enum' => ['foo'],
                ],
            ],
            'required' => ['aaa', 'bbb'],
            'additionalProperties' => false,
        ], $constraint->toJsonSchema());
    }

    public function test_is_converted_to_json_schema_with_optionals(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => (new StringConstraint())->optional(),
                'bbb' => (new EnumConstraint(['foo']))->default(null),
            ]
        );
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'aaa' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'bbb' => [
                    'type' => 'string',
                    'enum' => ['foo'],
                    'default' => null,
                ],
            ],
            'required' => ['bbb'],
            'additionalProperties' => false,
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => new StringConstraint(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );
        self::assertEquals([
            'type' => ['null', 'object'],
            'properties' => [
                'aaa' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'bbb' => [
                    'type' => 'string',
                    'enum' => ['foo'],
                ],
            ],
            'required' => ['aaa', 'bbb'],
            'additionalProperties' => false,
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates_defaults(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => (new StringConstraint())->default('hi'),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );

        $validatedResult = $constraint->validate([
            'bbb' => 'foo',
        ]);

        self::assertTrue($validatedResult->status->isValid());
        self::assertEquals([
            'aaa' => 'hi',
            'bbb' => 'foo',
        ], $validatedResult->castedData);
    }

    public function test_validates_fail(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => new StringConstraint(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );

        $validatedResult = $constraint->validate([
            'aaa' => 'hi',
            'bbb' => 'woot',
        ]);
        self::assertFalse($validatedResult->status->isValid());
        self::assertEquals([
            '' => 'invalid_nested_constraint',
            'bbb' => 'invalid_enum_value',
        ], $validatedResult->getErrors()->toArray());
    }

    public function test_validates_nullable(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => (new StringConstraint())->nullable(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );

        $validatedResult = $constraint->validate([
            'bbb' => 'foo',
        ]);

        self::assertTrue($validatedResult->status->isValid());
        self::assertEquals([
            'aaa' => null,
            'bbb' => 'foo',
        ], $validatedResult->castedData);
    }

    public function test_validates_nullable_null(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => (new StringConstraint())->nullable(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );

        $validatedResult = $constraint->validate([
            'aaa' => null,
            'bbb' => 'foo',
        ]);

        self::assertTrue($validatedResult->status->isValid());
        self::assertEquals([
            'aaa' => null,
            'bbb' => 'foo',
        ], $validatedResult->castedData);
    }

    public function test_validates_ok(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => new StringConstraint(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );

        $validatedResult = $constraint->validate([
            'aaa' => 'hi',
            'bbb' => 'foo',
        ]);

        self::assertTrue($validatedResult->status->isValid());
    }

    public function test_validates_optional(): void
    {
        $constraint = new ObjectConstraint(
            properties: [
                'aaa' => (new StringConstraint())->optional(),
                'bbb' => new EnumConstraint(['foo']),
            ]
        );

        $validatedResult = $constraint->validate([
            'bbb' => 'foo',
        ]);

        self::assertTrue($validatedResult->status->isValid());
        self::assertEquals([
            'bbb' => 'foo',
        ], $validatedResult->castedData);
    }
}
