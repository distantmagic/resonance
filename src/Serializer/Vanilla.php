<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Serializer;

use Distantmagic\Resonance\Serializer;

readonly class Vanilla extends Serializer
{
    public function serialize(mixed $data): string
    {
        return serialize($data);
    }

    public function unserialize(string $data): mixed
    {
        return unserialize($data);
    }
}
