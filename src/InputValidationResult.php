<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

/**
 * @template TValidatedModel of InputValidatedData
 */
readonly class InputValidationResult
{
    /**
     * @var Map<string,string> $errors
     */
    public Map $errors;

    /**
     * @param null|TValidatedModel $inputValidatedData
     */
    public function __construct(public ?InputValidatedData $inputValidatedData = null)
    {
        $this->errors = new Map();
    }

    public function getErrorMessage(): string
    {
        return $this->errors->values()->join("\n");
    }
}
