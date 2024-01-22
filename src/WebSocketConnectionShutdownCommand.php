<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class WebSocketConnectionShutdownCommand
{
    public function __construct(
        public int $fd,
    ) {}
}
