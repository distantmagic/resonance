<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;
use LogicException;

final readonly class ConstConstraint extends Constraint
{
    /**
     * @param float|int|non-empty-string $constValue
     */
    public function __construct(public float|int|string $constValue)
    {
        parent::__construct();
    }

    public function default(mixed $defaultValue): never
    {
        throw new LogicException('Const constraint cannot have a default value');
    }

    public function nullable(): never
    {
        throw new LogicException('Const constraint cannot be nullable');
    }

    public function optional(): never
    {
        throw new LogicException('Const constraint cannot be optional');
    }

    protected function doConvertToJsonSchema(): array
    {
        return [
            'const' => $this->constValue,
        ];
    }

    protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult
    {
        if ($notValidatedData !== $this->constValue) {
            return new ConstraintResult(
                castedData: $notValidatedData,
                path: $path,
                reason: ConstraintReason::InvalidEnumValue,
                status: ConstraintResultStatus::Invalid,
            );
        }

        return new ConstraintResult(
            castedData: $notValidatedData,
            path: $path,
            reason: ConstraintReason::Ok,
            status: ConstraintResultStatus::Valid,
        );
    }
}
