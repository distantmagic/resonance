<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum WebSocketConnectionStatus
{
    case Closed;
    case Open;

    public function isClosed(): bool
    {
        return self::Closed === $this;
    }

    public function isOpen(): bool
    {
        return self::Open === $this;
    }
}
