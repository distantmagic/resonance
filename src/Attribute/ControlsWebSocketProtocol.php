<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\WebSocketProtocol;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ControlsWebSocketProtocol extends BaseAttribute
{
    public function __construct(
        public WebSocketProtocol $webSocketProtocol,
    ) {}
}
