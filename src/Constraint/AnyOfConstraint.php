<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintDefaultValue;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;
use RuntimeException;

final readonly class AnyOfConstraint extends Constraint
{
    /**
     * @param array<Constraint> $anyOf
     */
    public function __construct(
        public array $anyOf,
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
            anyOf: $this->anyOf,
            defaultValue: new ConstraintDefaultValue($defaultValue),
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function nullable(): never
    {
        throw new RuntimeException('AnyOf constraint cannot be nullable');
    }

    public function optional(): self
    {
        return new self(
            anyOf: $this->anyOf,
            defaultValue: $this->defaultValue,
            isNullable: $this->isNullable,
            isRequired: false,
        );
    }

    protected function doConvertToJsonSchema(): array
    {
        /**
         * @var list<array> $anyOf
         */
        $anyOf = [];

        foreach ($this->anyOf as $constraint) {
            $anyOf[] = $constraint->toJsonSchema();
        }

        /**
         * @var array{
         *     anyOf: list<array>
         * }
         */
        return [
            'anyOf' => $anyOf,
        ];
    }

    protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult
    {
        foreach ($this->anyOf as $constraint) {
            $constraintResult = $constraint->validate($notValidatedData, $path);

            if ($constraintResult->status->isValid()) {
                return new ConstraintResult(
                    castedData: $notValidatedData,
                    path: $path,
                    reason: ConstraintReason::Ok,
                    status: ConstraintResultStatus::Valid,
                );
            }
        }

        return new ConstraintResult(
            castedData: $notValidatedData,
            path: $path,
            reason: ConstraintReason::InvalidNestedConstraint,
            status: ConstraintResultStatus::Invalid,
        );
    }
}
