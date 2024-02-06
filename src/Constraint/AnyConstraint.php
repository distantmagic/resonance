<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintDefaultValue;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;
use stdClass;

readonly class AnyConstraint extends Constraint
{
    public function default(mixed $defaultValue): self
    {
        return new self(
            defaultValue: new ConstraintDefaultValue($defaultValue),
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function nullable(): self
    {
        return new self(
            defaultValue: $this->defaultValue ?? new ConstraintDefaultValue(null),
            isNullable: true,
            isRequired: $this->isRequired,
        );
    }

    public function optional(): self
    {
        return new self(
            defaultValue: $this->defaultValue,
            isNullable: $this->isNullable,
            isRequired: false,
        );
    }

    protected function doConvertToJsonSchema(): object
    {
        return new stdClass();
    }

    protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult
    {
        return new ConstraintResult(
            castedData: $notValidatedData,
            path: $path,
            reason: ConstraintReason::InvalidEnumValue,
            status: ConstraintResultStatus::Valid,
        );
    }
}
