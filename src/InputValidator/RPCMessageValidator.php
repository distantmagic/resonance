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
use Nette\Schema\Expect;

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
        ]);
        // return Expect::structure([
        //     0 => Expect::anyOf(...$this->rpcMethodValidator->names())->required(),
        //     1 => Expect::mixed()->required(),
        //     2 => Expect::string()
        //         ->nullable()
        //         ->assert($this->isUuidNullable(...), 'Expected uuid')
        //         ->required(),
        // ])->castTo('array');
    }

    // private function isUuidNullable(?string $uuid): bool
    // {
    //     if (is_null($uuid)) {
    //         return true;
    //     }

    //     return uuid_is_valid($uuid);
    // }
}
