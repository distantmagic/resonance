<?php

declare(strict_types=1);

namespace Resonance\InputValidator;

use App\InputValidator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Resonance\InputValidatedData\RPCMessage;

/**
 * @extends InputValidator<RPCMessage, array{
 *     0: string,
 *     1: mixed,
 *     2: null|string,
 * }>
 */
readonly class RPCMessageValidator extends InputValidator
{
    public function castValidatedData(mixed $data): RPCMessage
    {
        return new RPCMessage($data[0], $data[1], $data[2]);
    }

    public function makeSchema(): Schema
    {
        return Expect::structure([
            0 => Expect::string()->min(1)->required(),
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