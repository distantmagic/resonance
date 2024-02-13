<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintDefaultValue;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;

final readonly class FilenameConstraint extends Constraint
{
    public function __construct(
        ?ConstraintDefaultValue $defaultValue = null,
        bool $isNullable = false,
        bool $isRequired = true,
        public bool $isDirectory = false,
        public bool $mustExist = true,
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
            isDirectory: $this->isDirectory,
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
            mustExist: $this->mustExist,
        );
    }

    public function nullable(): self
    {
        return new self(
            defaultValue: $this->defaultValue ?? new ConstraintDefaultValue(null),
            isDirectory: $this->isDirectory,
            isNullable: true,
            isRequired: $this->isRequired,
            mustExist: $this->mustExist,
        );
    }

    public function optional(): self
    {
        return new self(
            defaultValue: $this->defaultValue,
            isDirectory: $this->isDirectory,
            isNullable: $this->isNullable,
            isRequired: false,
            mustExist: $this->mustExist,
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

        $realPath = realpath($notValidatedData);

        if (false === $realPath || !file_exists($realPath)) {
            if (!$this->mustExist) {
                return new ConstraintResult(
                    castedData: $notValidatedData,
                    path: $path,
                    reason: ConstraintReason::Ok,
                    status: ConstraintResultStatus::Valid,
                );
            }

            return new ConstraintResult(
                castedData: $realPath,
                path: $path,
                reason: ConstraintReason::FileNotFound,
                status: ConstraintResultStatus::Invalid,
            );
        }

        if (!is_readable($realPath)) {
            return new ConstraintResult(
                castedData: $realPath,
                path: $path,
                reason: ConstraintReason::FileNotReadable,
                status: ConstraintResultStatus::Invalid,
            );
        }

        if ($this->isDirectory && !is_dir($realPath)) {
            return new ConstraintResult(
                castedData: $realPath,
                path: $path,
                reason: ConstraintReason::NotDirectory,
                status: ConstraintResultStatus::Invalid,
            );
        }

        return new ConstraintResult(
            castedData: $realPath,
            path: $path,
            reason: ConstraintReason::Ok,
            status: ConstraintResultStatus::Valid,
        );
    }
}
