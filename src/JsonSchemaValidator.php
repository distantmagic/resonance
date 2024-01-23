<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;

#[Singleton]
readonly class JsonSchemaValidator
{
    /**
     * @var Map<JsonSchemaSourceInterface,bool|object|string>
     */
    private Map $convertedSchemas;

    private ErrorFormatter $errorFormatter;
    private Validator $validator;

    public function __construct()
    {
        $this->convertedSchemas = new Map();
        $this->errorFormatter = new ErrorFormatter();
        $this->validator = new Validator();
    }

    public function validate(JsonSchemaSourceInterface $jsonSchemaSource, mixed $data): JsonSchemaValidationResult
    {
        $convertedSchema = $this->convertSchema($jsonSchemaSource);

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

    private function convertSchema(JsonSchemaSourceInterface $jsonSchemaSource): bool|object|string
    {
        if ($this->convertedSchemas->hasKey($jsonSchemaSource)) {
            return $this->convertedSchemas->get($jsonSchemaSource);
        }

        /**
         * @var bool|object|string $convertedSchema
         */
        $convertedSchema = Helper::toJSON($jsonSchemaSource->getSchema()->schema);

        $this->convertedSchemas->put($jsonSchemaSource, $convertedSchema);

        return $convertedSchema;
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
