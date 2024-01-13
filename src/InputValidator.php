<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use Opis\JsonSchema\Exceptions\ParseException;
use Swoole\Http\Request;

/**
 * @template TValidatedModel of InputValidatedData
 * @template TValidatedData
 */
abstract readonly class InputValidator
{
    public JsonSchema $jsonSchema;

    /**
     * @param TValidatedData $data
     *
     * @return TValidatedModel
     */
    abstract protected function castValidatedData(mixed $data): InputValidatedData;

    abstract protected function makeSchema(): JsonSchema;

    public function __construct(private JsonSchemaValidator $jsonSchemaValidator)
    {
        $this->jsonSchema = $this->makeSchema();
    }

    public function getSchema(): JsonSchema
    {
        return $this->jsonSchema;
    }

    /**
     * @return InputValidationResult<TValidatedModel>
     */
    public function validateData(mixed $data): InputValidationResult
    {
        try {
            /**
             * @var JsonSchemaValidationResult<TValidatedData>
             */
            $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($this->jsonSchema, $data);
        } catch (ParseException $parseException) {
            throw new LogicException(sprintf(
                'JSON schema is invalid in: "%s" "%s"',
                $this::class,
            ), 0, $parseException);
        }

        $errors = $jsonSchemaValidationResult->errors;

        if (empty($errors)) {
            return new InputValidationResult($this->castValidatedData($jsonSchemaValidationResult->data));
        }

        /**
         * @var InputValidationResult<TValidatedModel>
         */
        $validationResult = new InputValidationResult();

        foreach ($errors as $propertyName => $propertyErrors) {
            $validationResult->errors->put(
                $propertyName,
                implode("\n", $propertyErrors),
            );
        }

        return $validationResult;
    }

    /**
     * @return InputValidationResult<TValidatedModel>
     */
    public function validateRequest(Request $request): InputValidationResult
    {
        return $this->validateData($request->post);
    }
}
