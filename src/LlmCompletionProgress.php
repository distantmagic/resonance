<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class LlmCompletionProgress implements LlmCompletionProgressInterface
{
    public function __construct(
        private string $category,
        private bool $shouldNotify,
    ) {}

    public function getCategory(): string
    {
        return $this->category;
    }

    public function shouldNotify(): bool
    {
        return $this->shouldNotify;
    }
}
