<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\Validator;

#[Singleton]
readonly class JsonSchemaValidator
{
    private Factory $factory;

    public function __construct(ApplicationConfiguration $applicationConfiguration)
    {
        $productionFlags = Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_APPLY_DEFAULTS;

        $this->factory = new Factory(
            checkMode: Environment::Development === $applicationConfiguration->environment
                ? $productionFlags | Constraint::CHECK_MODE_VALIDATE_SCHEMA
                : $productionFlags,
        );
    }

    public function validate(JsonSchema $jsonSchema, mixed $data): JsonSchemaValidationResult
    {
        $validator = new Validator($this->factory);
        $validator->validate($data, $jsonSchema->schema);

        return new JsonSchemaValidationResult(
            validator: $validator,
            data: $data,
        );
    }
}
