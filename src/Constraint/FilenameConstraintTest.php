<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class FilenameConstraintTest extends TestCase
{
    public function test_invalid(): void
    {
        $constraint = new FilenameConstraint();

        self::assertFalse($constraint->validate(__FILE__.'.not_exists')->status->isValid());
    }

    public function test_is_converted_optionally_to_json_schema(): void
    {
        $constraint = new FilenameConstraint();

        self::assertEquals([
            'type' => 'string',
            'minLength' => 1,
        ], $constraint->optional()->toJsonSchema());
    }

    public function test_is_converted_to_json_schema(): void
    {
        $constraint = new FilenameConstraint();

        self::assertEquals([
            'type' => 'string',
            'minLength' => 1,
        ], $constraint->toJsonSchema());
    }

    public function test_nullable_is_converted_to_json_schema(): void
    {
        $constraint = new FilenameConstraint();

        self::assertEquals([
            'type' => ['null', 'string'],
            'minLength' => 1,
            'default' => null,
        ], $constraint->nullable()->toJsonSchema());
    }

    public function test_validates(): void
    {
        $constraint = new FilenameConstraint();

        self::assertTrue($constraint->validate(__FILE__)->status->isValid());
    }
}
