<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use RuntimeException;

#[Singleton]
readonly class JsonSchemaValidator
{
    /**
     * @var Map<JsonSchemaSourceInterface,Schema>
     */
    private Map $convertedSchemas;

    private ErrorFormatter $errorFormatter;
    private SchemaLoader $schemaLoader;
    private Validator $validator;

    public function __construct()
    {
        $this->convertedSchemas = new Map();
        $this->errorFormatter = new ErrorFormatter();
        $this->schemaLoader = new SchemaLoader(new SchemaParser(), null, false);
        $this->validator = new Validator($this->schemaLoader);
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

    private function convertSchema(JsonSchemaSourceInterface $jsonSchemaSource): Schema
    {
        if ($this->convertedSchemas->hasKey($jsonSchemaSource)) {
            return $this->convertedSchemas->get($jsonSchemaSource);
        }

        $convertedSchema = Helper::toJSON($jsonSchemaSource->getSchema()->schema);

        if (!is_object($convertedSchema)) {
            throw new RuntimeException('Json Schema must be an object');
        }

        $validatedSchema = $this->schemaLoader->loadObjectSchema($convertedSchema);

        $this->convertedSchemas->put($jsonSchemaSource, $validatedSchema);

        return $validatedSchema;
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
