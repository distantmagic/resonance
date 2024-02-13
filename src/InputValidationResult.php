<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TValidatedModel of InputValidatedData
 */
readonly class InputValidationResult
{
    /**
     * @param null|TValidatedModel $inputValidatedData
     */
    public function __construct(
        public ?InputValidatedData $inputValidatedData,
        public ConstraintResult $constraintResult,
    ) {}

    public function getErrorMessage(): string
    {
        return (string) (new ConstraintResultErrorMessage($this->constraintResult));
    }
}
