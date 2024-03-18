<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface SerializerInterface
{
    public function serialize(mixed $data): string;

    public function unserialize(string $data): mixed;
}
