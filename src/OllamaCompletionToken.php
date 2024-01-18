<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateTimeImmutable;
use Stringable;

readonly class OllamaCompletionToken implements Stringable
{
    public function __construct(
        public DateTimeImmutable $createdAt,
        public string $response,
    ) {}

    public function __toString(): string
    {
        return $this->response;
    }
}
