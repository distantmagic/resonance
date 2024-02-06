<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use RuntimeException;

class JsonSchemaValidationException extends RuntimeException
{
    /**
     * @param array<non-empty-string,Set<non-empty-string>> $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct((string) new JsonSchemaValidationErrorMessage($errors));
    }
}
