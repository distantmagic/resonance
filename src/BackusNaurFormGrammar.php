<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

abstract readonly class BackusNaurFormGrammar implements JsonSerializable
{
    abstract public function getGrammarContent(): string;

    public function jsonSerialize(): mixed
    {
        return $this->getGrammarContent();
    }
}
