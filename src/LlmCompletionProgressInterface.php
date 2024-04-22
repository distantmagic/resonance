<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface LlmCompletionProgressInterface
{
    public function getCategory(): string;

    public function shouldNotify(): bool;
}
