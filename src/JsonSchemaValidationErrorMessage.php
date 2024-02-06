<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use Stringable;

readonly class JsonSchemaValidationErrorMessage implements Stringable
{
    public string $message;

    /**
     * @param array<non-empty-string,Set<non-empty-string>> $errors
     */
    public function __construct(array $errors)
    {
        $messages = [];

        foreach ($errors as $propertyName => $propertyErrors) {
            foreach ($propertyErrors as $propertyError) {
                $messages[] = sprintf('"%s": "%s"', $propertyName, $propertyError);
            }
        }

        $this->message = sprintf(
            "Encountered validation errors:\n-> %s",
            implode("\n-> ", $messages)
        );
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
