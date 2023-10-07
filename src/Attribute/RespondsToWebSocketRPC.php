<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\RPCMethodInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RespondsToWebSocketRPC extends BaseAttribute
{
    public function __construct(
        public RPCMethodInterface $method,
    ) {}
}
