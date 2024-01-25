<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;
use Stringable;

abstract readonly class LlmPromptTemplate implements JsonSerializable, Stringable
{
    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
