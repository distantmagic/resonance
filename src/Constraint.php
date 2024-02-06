<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;

/**
 * @psalm-import-type PJsonSchema from JsonSchemableInterface
 */
abstract readonly class Constraint implements JsonSchemableInterface
{
    abstract public function default(mixed $defaultValue): self;

    abstract public function nullable(): self;

    abstract public function optional(): self;

    /**
     * @return PJsonSchema
     */
    abstract protected function doConvertToJsonSchema(): array|object;

    /**
     * @param mixed $notValidatedData explicitly mixed for typechecks
     */
    abstract protected function doValidate(mixed $notValidatedData, ConstraintPath $path): ConstraintResult;

    public function __construct(
        public ?ConstraintDefaultValue $defaultValue = null,
        public bool $isNullable = false,
        public bool $isRequired = true,
    ) {}

    /**
     * @return PJsonSchema
     */
    public function toJsonSchema(): array|object
    {
        $jsonSchema = $this->doConvertToJsonSchema();

        if (is_object($jsonSchema)) {
            return $jsonSchema;
        }

        if ($this->defaultValue) {
            /**
             * @var mixed explicitly mixed for typechecks
             */
            $jsonSchema['default'] = $this->defaultValue->defaultValue;
        }

        if ($this->isNullable) {
            if (!isset($jsonSchema['type'])) {
                throw new LogicException('Schema cannot be nullable without a type');
            }

            if (is_array($jsonSchema['type'])) {
                $jsonSchema['type'] = ['null', ...$jsonSchema['type']];
            } else {
                $jsonSchema['type'] = ['null', $jsonSchema['type']];
            }
        }

        /**
         * @var PJsonSchema
         */
        return $jsonSchema;
    }

    /**
     * @param mixed $notValidatedData explicitly mixed for typechecks
     */
    public function validate(
        mixed $notValidatedData,
        ConstraintPath $path = new ConstraintPath(),
    ): ConstraintResult {
        if ($this->isNullable && is_null($notValidatedData)) {
            return new ConstraintResult(
                castedData: null,
                path: $path,
                reason: ConstraintReason::Ok,
                status: ConstraintResultStatus::Valid,
            );
        }

        return $this->doValidate($notValidatedData, $path);
    }
}
