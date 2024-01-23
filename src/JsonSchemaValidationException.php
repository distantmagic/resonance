<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use RuntimeException;

class JsonSchemaValidationException extends RuntimeException
{
    /**
     * @param array<string,array<string>> $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct((string) new JsonSchemaValidationErrorMessage($errors));
    }
}
