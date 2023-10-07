<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class WebSocketResolvedProtocolController
{
    public function __construct(
        public WebSocketProtocol $protocol,
        public WebSocketProtocolControllerInterface $controller,
    ) {}
}
