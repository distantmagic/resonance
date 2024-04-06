<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum DialogueResponseResolutionStatus
{
    case CannotRespond;
    case CanRespond;
    case Failed;

    public function canRespond(): bool
    {
        return self::CanRespond === $this;
    }

    public function isFailed(): bool
    {
        return self::Failed === $this;
    }
}
