<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

readonly class ConstraintPath implements Stringable
{
    /**
     * @param array<string> $path
     */
    public function __construct(private array $path = []) {}

    public function __toString(): string
    {
        return implode('.', $this->path);
    }

    public function fork(string $next): self
    {
        return new self([...$this->path, $next]);
    }
}
