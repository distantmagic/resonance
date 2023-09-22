<?php

declare(strict_types=1);

namespace Resonance\InputValidator;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Resonance\Attribute\Singleton;
use Resonance\InputValidatedData\RPCMessage;
use Resonance\InputValidator;
use Resonance\RPCMethodValidatorInterface;

/**
 * @extends InputValidator<RPCMessage, array{
 *     0: string,
 *     1: mixed,
 *     2: null|string,
 * }>
 */
#[Singleton]
readonly class RPCMessageValidator extends InputValidator
{
    public function __construct(private RPCMethodValidatorInterface $rpcMethodValidator)
    {
        parent::__construct();
    }

    protected function castValidatedData(mixed $data): RPCMessage
    {
        return new RPCMessage(
            $this->rpcMethodValidator->castToRPCMethod($data[0]),
            $data[1],
            $data[2],
        );
    }

    protected function makeSchema(): Schema
    {
        return Expect::structure([
            0 => Expect::anyOf(...$this->rpcMethodValidator->cases())->required(),
            1 => Expect::mixed()->required(),
            2 => Expect::string()
                ->nullable()
                ->assert($this->isUuidNullable(...), 'Expected uuid')
                ->required(),
        ])->castTo('array');
    }

    private function isUuidNullable(?string $uuid): bool
    {
        if (is_null($uuid)) {
            return true;
        }

        return uuid_is_valid($uuid);
    }
}
