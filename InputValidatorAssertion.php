<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TInputData
 */
abstract readonly class InputValidatorAssertion
{
    /**
     * @param TInputData $data
     */
    abstract public function assert(mixed $data): bool;

    abstract public function getMessage(): string;
}
