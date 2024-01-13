<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TValidatedData
 */
readonly class JsonSchemaValidationResult
{
    /**
     * @param TValidatedData              $data
     * @param array<string,array<string>> $errors
     */
    public function __construct(
        public mixed $data,
        public array $errors,
    ) {}
}
