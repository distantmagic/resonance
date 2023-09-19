<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use App\WebSocketProtocol;
use Attribute;
use Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ControlsWebSocketProtocol extends BaseAttribute
{
    public function __construct(
        public WebSocketProtocol $webSocketProtocol,
    ) {}
}
