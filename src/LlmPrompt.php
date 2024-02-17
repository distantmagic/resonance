<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class LlmPrompt
{
    abstract public function getPromptContent(): string;
}
