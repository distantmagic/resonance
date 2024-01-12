<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSchema\Validator;

/**
 * @template TValidatedData
 */
readonly class JsonSchemaValidationResult
{
    /**
     * @param TValidatedData $data
     */
    public function __construct(
        public Validator $validator,
        public mixed $data,
    ) {}
}
