<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

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

    /**
     * @return InputValidationResult<TValidatedModel>
     */
    public function validateData(mixed $data): InputValidationResult
    {
        /**
         * @var JsonSchemaValidationResult<TValidatedData>
         */
        $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($this->jsonSchema, $data);

        if ($jsonSchemaValidationResult->validator->isValid()) {
            return new InputValidationResult($this->castValidatedData($jsonSchemaValidationResult->data));
        }

        /**
         * @var InputValidationResult<TValidatedModel>
         */
        $validationResult = new InputValidationResult();

        /**
         * @var array{
         *   property: string,
         *   message: string
         * } $error
         */
        foreach ($jsonSchemaValidationResult->validator->getErrors() as $error) {
            $validationResult->errors->put(
                $error['property'],
                $error['message'],
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
