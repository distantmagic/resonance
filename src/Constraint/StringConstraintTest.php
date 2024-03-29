<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\ConstraintStringFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(StringConstraint::class)]
final class StringConstraintTest extends TestCase
{
    public function test_allows_empty(): void
    {
        $constraint = new StringConstraint();

        self::assertTrue($constraint->allowEmpty()->validate('')->status->isValid());
    }

    public function test_disallows_empty(): void
    {
        $constraint = new StringConstraint();

        self::assertFalse($constraint->validate('')->status->isValid());
    }

    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new StringConstraint();

        self::assertEquals([
            'type' => 'string',
            'minLength' => 1,
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new StringConstraint();

        self::assertEquals([
            'type' => 'string',
            'minLength' => 1,
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new StringConstraint();

        self::assertEquals([
            'type' => ['null', 'string'],
            'minLength' => 1,
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new StringConstraint();

        self::assertTrue($constraint->validate('hi')->status->isValid());
    }

    public function test_validates_mail(): void
    {
        $constraint = new StringConstraint(format: ConstraintStringFormat::Mail);

        self::assertFalse($constraint->validate('hi')->status->isValid());
        self::assertTrue($constraint->validate('test@example.com')->status->isValid());
    }

    public function test_validates_uuid(): void
    {
        $constraint = new StringConstraint(format: ConstraintStringFormat::Uuid);

        self::assertFalse($constraint->validate('hi')->status->isValid());
        self::assertTrue($constraint->validate('ccaf9acc-123e-4ff3-85da-1117342b0e02')->status->isValid());
    }
}
