<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidator;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\AnyConstraint;
use Distantmagic\Resonance\Constraint\ConstConstraint;
use Distantmagic\Resonance\Constraint\EnumConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\InputValidatedData\JsonRPCMessage;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\JsonRPCMethodValidatorInterface;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @extends InputValidator<JsonRPCMessage, array{
 *     id: null|non-empty-string,
 *     jsonrpc: '2.0',
 *     method: non-empty-string,
 *     params: mixed,
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
            $this->rpcMethodValidator->castToRPCMethod($data['method']),
            $data['params'],
            $data['id'],
        );
    }

    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'id' => (new StringConstraint())->optional()->default(null),
                'jsonrpc' => new ConstConstraint('2.0'),
                'method' => new EnumConstraint($this->rpcMethodValidator->values()),
                'params' => new AnyConstraint(),
            ]
        );
    }
}
