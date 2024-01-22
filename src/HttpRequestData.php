<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use RuntimeException;

readonly class HttpRequestData
{
    /**
     * @psalm-taint-source user_data $data
     */
    public function __construct(private mixed $data) {}

    public function get(string $name, ?string $default = null): ?string
    {
        if (!is_array($this->data) || !isset($this->data[$name])) {
            return $default;
        }

        $value = $this->data[$name];

        if (!is_string($value)) {
            throw new RuntimeException('Expected request data to be string: '.$name);
        }

        return $value;
    }
}
