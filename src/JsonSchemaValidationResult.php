<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

/**
 * @template TValidatedData
 */
readonly class JsonSchemaValidationResult
{
    /**
     * @param TValidatedData                                $data
     * @param array<non-empty-string,Set<non-empty-string>> $errors
     */
    public function __construct(
        public mixed $data,
        public array $errors,
    ) {}
}
