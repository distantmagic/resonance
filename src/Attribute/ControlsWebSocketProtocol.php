<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\WebSocketProtocol;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ControlsWebSocketProtocol extends BaseAttribute
{
    public function __construct(
        public WebSocketProtocol $webSocketProtocol,
    ) {}
}
