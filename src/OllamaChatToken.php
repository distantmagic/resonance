<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateTimeImmutable;
use Stringable;

readonly class OllamaChatToken implements Stringable
{
    public function __construct(
        public DateTimeImmutable $createdAt,
        public OllamaChatMessage $message,
    ) {}

    public function __toString(): string
    {
        return (string) $this->message;
    }
}
