<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidator;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\AnyConstraint;
use Distantmagic\Resonance\Constraint\EnumConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\Constraint\TupleConstraint;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\InputValidatedData\JsonRPCMessage;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\JsonRPCMethodValidatorInterface;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @extends InputValidator<JsonRPCMessage, array{
 *     0: string,
 *     1: mixed,
 *     2: null|string,
 * }>
 */
#[GrantsFeature(Feature::WebSocket)]
#[Singleton(collection: SingletonCollection::InputValidator)]
readonly class JsonRPCMessageValidator extends InputValidator
{
    public function __construct(private JsonRPCMethodValidatorInterface $rpcMethodValidator) {}

    public function castValidatedData(mixed $data): JsonRPCMessage
    {
        return new JsonRPCMessage(
            $this->rpcMethodValidator->castToRPCMethod($data[0]),
            $data[1],
            $data[2],
        );
    }

    public function getConstraint(): Constraint
    {
        return new TupleConstraint(
            items: [
                new EnumConstraint($this->rpcMethodValidator->values()),
                new AnyConstraint(),
                (new StringConstraint())->nullable(),
            ],
        );
    }
}
