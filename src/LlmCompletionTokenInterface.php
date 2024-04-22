<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface LlmCompletionTokenInterface
{
    public function getContent(): string;

    public function isFailed(): bool;

    public function isLastToken(): bool;
}
