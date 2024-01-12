<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidator;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\InputValidatedData\RPCMessage;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\JsonSchemaValidator;
use Distantmagic\Resonance\RPCMethodValidatorInterface;

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
    public function __construct(
        JsonSchemaValidator $jsonSchemaValidator,
        private RPCMethodValidatorInterface $rpcMethodValidator,
    ) {
        parent::__construct($jsonSchemaValidator);
    }

    protected function castValidatedData(mixed $data): RPCMessage
    {
        return new RPCMessage(
            $this->rpcMethodValidator->castToRPCMethod($data[0]),
            $data[1],
            $data[2],
        );
    }

    protected function makeSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'array',
            'items' => [
                [
                    'type' => 'string',
                    'enum' => $this->rpcMethodValidator->names(),
                    'required' => true,
                ],
                [
                    'required' => true,
                ],
                [
                    'type' => 'string',
                    'format' => 'uuid',
                    'nullable' => true,
                    'required' => true,
                ],
            ],
        ]);
    }
}
