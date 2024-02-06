<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintDefaultValue;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;

final readonly class TupleConstraint extends Constraint
{
    /**
     * @param list<Constraint> $items
     */
    public function __construct(
        public array $items,
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
            items: $this->items,
            defaultValue: new ConstraintDefaultValue($defaultValue),
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function nullable(): self
    {
        return new self(
            items: $this->items,
            defaultValue: $this->defaultValue ?? new ConstraintDefaultValue(null),
            isNullable: true,
            isRequired: $this->isRequired,
        );
    }

    public function optional(): self
    {
        return new self(
            items: $this->items,
            defaultValue: $this->defaultValue,
            isNullable: $this->isNullable,
            isRequired: false,
        );
    }

    protected function doConvertToJsonSchema(): array
    {
        $prefixItems = [];

        foreach ($this->items as $item) {
            $prefixItems[] = $item->toJsonSchema();
        }

        return [
            'type' => 'array',
            'items' => false,
            'prefixItems' => $prefixItems,
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

        $i = 0;

        /**
         * @var list<mixed> explicitly mixed for typechecks
         */
        $ret = [];

        /**
         * @var list<ConstraintResult>
         */
        $invalidChildStatuses = [];

        /**
         * @var mixed $item explicitly mixed for typechecks
         */
        foreach ($this->items as $validator) {
            if (!array_key_exists($i, $notValidatedData)) {
                if ($validator->defaultValue) {
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $ret[] = $validator->defaultValue->defaultValue;
                } elseif ($validator->isRequired) {
                    $invalidChildStatuses[] = new ConstraintResult(
                        castedData: null,
                        path: $path->fork((string) $i),
                        reason: ConstraintReason::MissingProperty,
                        status: ConstraintResultStatus::Invalid,
                    );
                } else {
                    $ret[] = null;
                }
            } else {
                $childResult = $validator->validate(
                    notValidatedData: $notValidatedData[$i],
                    path: $path->fork((string) $i)
                );

                if ($childResult->status->isValid()) {
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $ret[] = $childResult->castedData;
                } else {
                    $invalidChildStatuses[] = $childResult;
                }
            }

            ++$i;
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
