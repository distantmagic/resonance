<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Serializer;

use Distantmagic\Resonance\Serializer;

readonly class Igbinary extends Serializer
{
    public function serialize(mixed $data): string
    {
        return igbinary_serialize($data);
    }

    public function unserialize(string $data): mixed
    {
        return igbinary_unserialize($data);
    }
}
