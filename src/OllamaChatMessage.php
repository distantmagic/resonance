<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;
use Stringable;

readonly class OllamaChatMessage implements JsonSerializable, Stringable
{
    public function __construct(
        public string $content,
        public OllamaChatRole $role,
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public function jsonSerialize(): array
    {
        return [
            'content' => $this->content,
            'role' => $this->role->value,
        ];
    }
}
