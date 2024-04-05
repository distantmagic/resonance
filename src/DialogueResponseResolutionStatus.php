<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum DialogueResponseResolutionStatus
{
    case CannotRespond;
    case CanRespond;

    public function canRespond(): bool
    {
        return self::CanRespond === $this;
    }
}
