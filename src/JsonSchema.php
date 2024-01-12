<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class JsonSchema implements JsonSerializable
{
    public function __construct(public array $schema) {}

    public function jsonSerialize(): array
    {
        return $this->schema;
    }
}
