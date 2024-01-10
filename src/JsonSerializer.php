<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class JsonSerializer
{
    public function __construct(private ApplicationConfiguration $applicationConfiguration) {}

    public function serialize(mixed $data): string
    {
        return json_encode(
            value: $data,
            flags: Environment::Production === $this->applicationConfiguration->environment
                ? JSON_THROW_ON_ERROR
                : JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        );
    }
}
