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
        $errors = $constraintResult->getErrors();
        $message = [];

        foreach ($errors as $name => $errorCode) {
            $message[] = sprintf('"%s" -> %s', $name, $errorCode);
        }

        var_dump($constraintResult->castedData);

        parent::__construct(sprintf(
            "%s:\n%s",
            $constraintId,
            implode("\n", $message),
        ));
    }
}
