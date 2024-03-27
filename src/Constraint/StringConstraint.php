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
use Ramsey\Uuid\Uuid;
use RuntimeException;

final readonly class StringConstraint extends Constraint
{
    public function __construct(
        public ?ConstraintStringFormat $format = null,
        ?ConstraintDefaultValue $defaultValue = null,
        private bool $isEmptyAllowed = false,
        bool $isNullable = false,
        bool $isRequired = true,
    ) {
        parent::__construct(
            defaultValue: $defaultValue,
            isNullable: $isNullable,
            isRequired: $isRequired,
        );
    }

    public function allowEmpty(): self
    {
        return new self(
            defaultValue: $this->defaultValue,
            format: $this->format,
            isEmptyAllowed: true,
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function default(mixed $defaultValue): self
    {
        return new self(
            defaultValue: new ConstraintDefaultValue($defaultValue),
            format: $this->format,
            isEmptyAllowed: $this->isEmptyAllowed,
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function nullable(): self
    {
        return new self(
            defaultValue: $this->defaultValue ?? new ConstraintDefaultValue(null),
            format: $this->format,
            isEmptyAllowed: $this->isEmptyAllowed,
            isNullable: true,
            isRequired: $this->isRequired,
        );
    }

    public function optional(): self
    {
        return new self(
            defaultValue: $this->defaultValue,
            format: $this->format,
            isEmptyAllowed: $this->isEmptyAllowed,
            isNullable: $this->isNullable,
            isRequired: false,
        );
    }

    protected function doConvertToJsonSchema(): array
    {
        return [
            'type' => 'string',
            'minLength' => $this->isEmptyAllowed ? 0 : 1,
        ];
    }

    protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult
    {
        if (!is_string($notValidatedData) || (!$this->isEmptyAllowed && 0 === strlen($notValidatedData))) {
            return new ConstraintResult(
                castedData: $notValidatedData,
                comment: 'Expected string, got '.gettype($notValidatedData),
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

        $isFormatValid = match ($this->format) {
            ConstraintStringFormat::Mail => false !== filter_var($notValidatedData, FILTER_VALIDATE_EMAIL),
            ConstraintStringFormat::Uuid => Uuid::isValid($notValidatedData),
            default => throw new RuntimeException('Unknown string format'),
        };

        if ($isFormatValid) {
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
}
