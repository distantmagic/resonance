<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidator;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\InputValidatedData\RPCMessage;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\RPCMethodValidatorInterface;
use stdClass;

/**
 * @extends InputValidator<RPCMessage, array{
 *     0: string,
 *     1: mixed,
 *     2: null|string,
 * }>
 */
#[Singleton(grantsFeature: Feature::WebSocket)]
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

    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'array',
            'items' => false,
            'prefixItems' => [
                [
                    'type' => 'string',
                    'enum' => $this->rpcMethodValidator->values(),
                ],
                new stdClass(),
                [
                    'type' => ['null', 'string'],
                    'format' => 'uuid',
                ],
            ],
        ]);
    }
}
