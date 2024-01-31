<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\ServerPipeMessage;

use Distantmagic\Resonance\ServerPipeMessage;

readonly class CloseWebSocketConnection extends ServerPipeMessage
{
    public function __construct(public int $fd) {}
}
