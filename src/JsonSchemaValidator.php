<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use JsonSchema\Constraints\Factory;
use JsonSchema\Validator;

#[Singleton]
readonly class JsonSchemaValidator
{
    private Factory $factory;

    public function __construct()
    {
        $this->factory = new Factory();
    }

    public function validate(JsonSchema $jsonSchema, mixed $data): Validator
    {
        $validator = new Validator($this->factory);
        $validator->validate($data);

        return $validator;
    }
}
