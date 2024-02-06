<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintDefaultValue;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;
use Distantmagic\Resonance\ConstraintStringFormat;
use RuntimeException;

final readonly class StringConstraint extends Constraint
{
    public function __construct(
        public ?ConstraintStringFormat $format = null,
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
            defaultValue: new ConstraintDefaultValue($defaultValue),
            format: $this->format,
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function nullable(): self
    {
        return new self(
            defaultValue: $this->defaultValue ?? new ConstraintDefaultValue(null),
            format: $this->format,
            isNullable: true,
            isRequired: $this->isRequired,
        );
    }

    public function optional(): self
    {
        return new self(
            defaultValue: $this->defaultValue,
            format: $this->format,
            isNullable: $this->isNullable,
            isRequired: false,
        );
    }

    protected function doConvertToJsonSchema(): array
    {
        return [
            'type' => 'string',
            'minLength' => 1,
        ];
    }

    protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult
    {
        if (!is_string($notValidatedData) || empty($notValidatedData)) {
            return new ConstraintResult(
                castedData: $notValidatedData,
                path: $path,
                reason: ConstraintReason::InvalidDataType,
                status: ConstraintResultStatus::Invalid,
            );
        }

        if (!$this->format) {
            return new ConstraintResult(
                castedData: $notValidatedData,
                path: $path,
                reason: ConstraintReason::Ok,
                status: ConstraintResultStatus::Valid,
            );
        }

        if (ConstraintStringFormat::Uuid === $this->format) {
            if (uuid_is_valid($notValidatedData)) {
                return new ConstraintResult(
                    castedData: $notValidatedData,
                    path: $path,
                    reason: ConstraintReason::Ok,
                    status: ConstraintResultStatus::Valid,
                );
            }

            return new ConstraintResult(
                castedData: $notValidatedData,
                path: $path,
                reason: ConstraintReason::InvalidFormat,
                status: ConstraintResultStatus::Invalid,
            );
        }

        throw new RuntimeException('Unknown string format');
    }
}