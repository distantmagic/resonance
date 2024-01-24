<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use RuntimeException;

#[Singleton]
readonly class JsonSerializer
{
    public function __construct(private ApplicationConfiguration $applicationConfiguration)
    {
        if (!function_exists('swoole_substr_json_decode')) {
            throw new RuntimeException('You need to compile Swoole with JSON support');
        }
    }

    public function serialize(mixed $data): string
    {
        return json_encode(
            value: $data,
            flags: Environment::Production === $this->applicationConfiguration->environment
                ? JSON_THROW_ON_ERROR
                : JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        );
    }

    public function unserialize(
        string $json,
        int $offset = 0,
        bool $throw = true,
    ): mixed {
        return swoole_substr_json_decode(
            flags: $throw ? JSON_THROW_ON_ERROR : 0,
            offset: $offset,
            str: $json,
        );
    }
}
