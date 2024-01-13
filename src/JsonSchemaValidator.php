<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;

#[Singleton]
readonly class JsonSchemaValidator
{
    private ErrorFormatter $errorFormatter;
    private Validator $validator;

    public function __construct()
    {
        $this->errorFormatter = new ErrorFormatter();
        $this->validator = new Validator();
    }

    public function validate(JsonSchema $jsonSchema, mixed $data): JsonSchemaValidationResult
    {
        /**
         * @var bool|object|string $convertedSchema
         */
        $convertedSchema = Helper::toJSON($jsonSchema->schema);

        /**
         * @var bool|object|string $convertedData
         */
        $convertedData = Helper::toJSON($data);

        $validationResult = $this->validator->validate($convertedData, $convertedSchema);

        return new JsonSchemaValidationResult(
            data: $convertedData,
            errors: $this->formatErrors($validationResult),
        );
    }

    /**
     * @return array<string,array<string>>
     */
    private function formatErrors(ValidationResult $validationResult): array
    {
        $error = $validationResult->error();

        if (empty($error)) {
            return [];
        }

        /**
         * @var array<string,array<string>>
         */
        return $this->errorFormatter->formatKeyed($error);
    }
}
