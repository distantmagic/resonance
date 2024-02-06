<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use RuntimeException;

class ConstraintValidationException extends RuntimeException
{
    public function __construct(
        string $constraintId,
        ConstraintResult $constraintResult,
    ) {
        parent::__construct((string) new ConstraintResultErrorMessage(
            $constraintId,
            $constraintResult,
        ));
    }
}
