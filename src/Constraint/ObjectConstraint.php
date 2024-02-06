<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Constraint;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ConstraintDefaultValue;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintReason;
use Distantmagic\Resonance\ConstraintResult;
use Distantmagic\Resonance\ConstraintResultStatus;

final readonly class ObjectConstraint extends Constraint
{
    /**
     * @param array<non-empty-string,Constraint> $properties
     */
    public function __construct(
        public array $properties = [],
        public bool $additionalProperties = false,
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

    public function additionalProperties(bool $allow): self
    {
        return new self(
            additionalProperties: $allow,
            properties: $this->properties,
            defaultValue: $this->defaultValue,
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function default(mixed $defaultValue): self
    {
        return new self(
            additionalProperties: $this->additionalProperties,
            properties: $this->properties,
            defaultValue: new ConstraintDefaultValue($defaultValue),
            isNullable: $this->isNullable,
            isRequired: $this->isRequired,
        );
    }

    public function nullable(): self
    {
        return new self(
            additionalProperties: $this->additionalProperties,
            properties: $this->properties,
            defaultValue: $this->defaultValue ?? new ConstraintDefaultValue(null),
            isNullable: true,
            isRequired: $this->isRequired,
        );
    }

    public function optional(): self
    {
        return new self(
            additionalProperties: $this->additionalProperties,
            properties: $this->properties,
            defaultValue: $this->defaultValue,
            isNullable: $this->isNullable,
            isRequired: false,
        );
    }

    protected function doConvertToJsonSchema(): array
    {
        $convertedProperties = [];
        $requiredProperties = [];

        foreach ($this->properties as $name => $property) {
            $convertedProperties[$name] = $property->toJsonSchema();

            if ($property->isRequired) {
                $requiredProperties[] = $name;
            }
        }

        return [
            'type' => 'object',
            'properties' => $convertedProperties,
            'required' => $requiredProperties,
            'additionalProperties' => $this->additionalProperties,
        ];
    }

    protected function getProperty(array|object $notValidatedData, string $propertyName): mixed
    {
        if (is_array($notValidatedData)) {
            return $notValidatedData[$propertyName];
        }

        return $notValidatedData->{$propertyName};
    }

    protected function hasProperty(array|object $notValidatedData, string $propertyName): bool
    {
        if (is_array($notValidatedData)) {
            return array_key_exists($propertyName, $notValidatedData);
        }

        return property_exists($notValidatedData, $propertyName);
    }

    /**
     * @psalm-suppress UnusedForeachValue we need foreach here
     */
    protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult
    {
        if (!is_array($notValidatedData) && !is_object($notValidatedData)) {
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

        foreach ($this->properties as $name => $validator) {
            if (!$this->hasProperty($notValidatedData, $name)) {
                if ($validator->defaultValue) {
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $ret[$name] = $validator->defaultValue->defaultValue;
                } elseif ($validator->isRequired) {
                    $invalidChildStatuses[] = new ConstraintResult(
                        castedData: null,
                        path: $path->fork($name),
                        reason: ConstraintReason::MissingProperty,
                        status: ConstraintResultStatus::Invalid,
                    );
                }
            } else {
                $childResult = $validator->validate(
                    notValidatedData: $this->getProperty($notValidatedData, $name),
                    path: $path->fork($name)
                );

                if ($childResult->status->isValid()) {
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $ret[$name] = $childResult->castedData;
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

        if (!$this->additionalProperties) {
            /**
             * @var array-key|string $notValidatedKey
             * @var mixed $notValidatedValue explicitly mixed for typechecks
             */
            foreach ($notValidatedData as $notValidatedKey => $notValidatedValue) {
                if (!array_key_exists($notValidatedKey, $this->properties)) {
                    return new ConstraintResult(
                        castedData: $notValidatedData,
                        path: $path->fork((string) $notValidatedKey),
                        reason: ConstraintReason::UnexpectedProperty,
                        status: ConstraintResultStatus::Invalid,
                    );
                }
            }
        }

        return new ConstraintResult(
            castedData: $ret,
            path: $path,
            reason: ConstraintReason::Ok,
            status: ConstraintResultStatus::Valid,
        );
    }
}
