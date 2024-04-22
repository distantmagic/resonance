<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 */
readonly class LlamaCppCompletionToken implements LlmCompletionTokenInterface, Stringable
{
    public function __construct(
        private string $content,
        private bool $isFailed,
        private bool $isLastToken,
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isFailed(): bool
    {
        return $this->isFailed;
    }

    public function isLastToken(): bool
    {
        return $this->isLastToken;
    }
}
