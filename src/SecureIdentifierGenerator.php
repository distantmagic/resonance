<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class SecureIdentifierGenerator
{
    public function generate(int $byteLength = 32): string
    {
        return bin2hex(random_bytes($byteLength));
    }
}
