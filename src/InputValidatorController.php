<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class InputValidatorController
{
    public function __construct(private JsonSchemaValidator $jsonSchemaValidator) {}

    /**
     * @template TCastedData of InputValidatedData
     * @template TValidatedData
     *
     * @param InputValidator<TCastedData,TValidatedData> $inputValidator
     *
     * @return InputValidationResult<TCastedData>
     */
    public function validateData(InputValidator $inputValidator, mixed $data): InputValidationResult
    {
        $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($inputValidator, $data);

        return $this->castJsonSchemaValidationResult($inputValidator, $jsonSchemaValidationResult);
    }

    /**
     * @template TCastedData of InputValidatedData
     * @template TValidatedData
     *
     * @param InputValidator<TCastedData,TValidatedData> $inputValidator
     * @param JsonSchemaValidationResult<TValidatedData> $jsonSchemaValidationResult
     *
     * @return InputValidationResult<TCastedData>
     */
    protected function castJsonSchemaValidationResult(
        InputValidator $inputValidator,
        JsonSchemaValidationResult $jsonSchemaValidationResult,
    ): InputValidationResult {
        $errors = $jsonSchemaValidationResult->errors;

        if (empty($errors)) {
            return new InputValidationResult($inputValidator->castValidatedData($jsonSchemaValidationResult->data));
        }

        /**
         * @var InputValidationResult<TCastedData>
         */
        $validationResult = new InputValidationResult();

        foreach ($errors as $propertyName => $propertyErrors) {
            $validationResult->errors->put(
                $propertyName,
                $propertyErrors,
            );
        }

        return $validationResult;
    }
}
