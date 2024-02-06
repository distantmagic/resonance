<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;

#[Singleton]
readonly class InputValidatorController
{
    /**
     * @var Map<InputValidator,Constraint>
     */
    public Map $cachedConstraints;

    public function __construct()
    {
        $this->cachedConstraints = new Map();
    }

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
        $constraintResult = $this->cachedConstraints->get($inputValidator)->validate($data);

        return $this->castJsonSchemaValidationResult($constraintResult, $inputValidator);
    }

    /**
     * @template TCastedData of InputValidatedData
     * @template TValidatedData
     *
     * @param InputValidator<TCastedData,TValidatedData> $inputValidator
     *
     * @return InputValidationResult<TCastedData>
     */
    protected function castJsonSchemaValidationResult(
        ConstraintResult $constraintResult,
        InputValidator $inputValidator,
    ): InputValidationResult {
        if ($constraintResult->status->isValid()) {
            /**
             * @var TValidatedData $constraintResult->castedData
             */
            return new InputValidationResult(
                $inputValidator->castValidatedData($constraintResult->castedData),
                $constraintResult,
            );
        }

        /**
         * @var InputValidationResult<TCastedData>
         */
        return new InputValidationResult(null, $constraintResult);
    }
}
