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
use Distantmagic\Resonance\ConstraintStringFormat;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\InputValidatedData\RPCMessage;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\RPCMethodValidatorInterface;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @extends InputValidator<RPCMessage, array{
 *     0: string,
 *     1: mixed,
 *     2: null|string,
 * }>
 */
#[GrantsFeature(Feature::WebSocket)]
#[Singleton(collection: SingletonCollection::InputValidator)]
readonly class RPCMessageValidator extends InputValidator
{
    public function __construct(private RPCMethodValidatorInterface $rpcMethodValidator) {}

    public function castValidatedData(mixed $data): RPCMessage
    {
        return new RPCMessage(
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
                (new StringConstraint(format: ConstraintStringFormat::Uuid))->nullable(),
            ],
        );
    }
}
