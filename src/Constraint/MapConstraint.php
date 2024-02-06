<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintDefaultValue;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;

final readonly class MapConstraint extends Constraint
{
    public function __construct(
        public Constraint $valueConstraint,
        ?ConstraintDefaultValue $defaultValue = null,
        bool $isNullable = false,
        bool $isRequired = true,
    ) {
        parent::__construct(
            defaultValue: $defaultValue,
            isNullable: $isNullable,
            isRequired: $isRequired,
        );
    }

    public function default(mixed $defaultValue): self
    {
        return new self(
            valueConstraint: $this->valueConstraint,
            defaultValue: new ConstraintDefaultValue($defaultValue),
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function nullable(): self
    {
        return new self(
            valueConstraint: $this->valueConstraint,
            defaultValue: $this->defaultValue ?? new ConstraintDefaultValue(null),
            isNullable: true,
            isRequired: $this->isRequired,
        );
    }

    public function optional(): self
    {
        return new self(
            valueConstraint: $this->valueConstraint,
            defaultValue: $this->defaultValue,
            isNullable: $this->isNullable,
            isRequired: false,
        );
    }

    protected function doConvertToJsonSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => $this->valueConstraint->toJsonSchema(),
        ];
    }

    protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult
    {
        if (!is_array($notValidatedData)) {
            return new ConstraintResult(
                castedData: $notValidatedData,
                path: $path,
                reason: ConstraintReason::InvalidDataType,
                status: ConstraintResultStatus::Invalid,
            );
        }

        $ret = [];

        /**
         * @var list<ConstraintResult>
         */
        $invalidChildStatuses = [];

        /**
         * @var mixed $notValidatedKey explicitly mixed for typechecks
         * @var mixed $notValidatedValue explicitly mixed for typechecks
         */
        foreach ($notValidatedData as $notValidatedKey => $notValidatedValue) {
            if (!is_string($notValidatedKey)) {
                $invalidChildStatuses[] = new ConstraintResult(
                    castedData: null,
                    path: $path,
                    reason: ConstraintReason::InvalidDataType,
                    status: ConstraintResultStatus::Invalid,
                );
            } else {
                $childResult = $this->valueConstraint->validate(
                    notValidatedData: $notValidatedValue,
                    path: $path->fork($notValidatedKey)
                );

                if ($childResult->status->isValid()) {
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $ret[$notValidatedKey] = $childResult->castedData;
                } else {
                    $invalidChildStatuses[] = $childResult;
                }
            }
        }

        if (!empty($invalidChildStatuses)) {
            return new ConstraintResult(
                castedData: $notValidatedData,
                nested: $invalidChildStatuses,
                path: $path,
                reason: ConstraintReason::InvalidNestedConstraint,
                status: ConstraintResultStatus::Invalid,
            );
        }

        return new ConstraintResult(
            castedData: $ret,
            path: $path,
            reason: ConstraintReason::Ok,
            status: ConstraintResultStatus::Valid,
        );
    }
}
