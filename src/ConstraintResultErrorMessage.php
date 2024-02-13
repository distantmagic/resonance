<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

readonly class ConstraintResultErrorMessage implements Stringable
{
    public string $message;

    public function __construct(ConstraintResult $constraintResult)
    {
        $errors = $constraintResult->getErrors();
        $message = [];

        foreach ($errors as $name => $errorCode) {
            $message[] = sprintf('"%s" -> %s', $name, $errorCode);
        }

        // var_dump($constraintResult->castedData);

        $this->message = sprintf(
            '%s',
            implode("\n", $message),
        );
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
