<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\JsonRPCMethodInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RespondsToWebSocketJsonRPC extends BaseAttribute
{
    public function __construct(
        public JsonRPCMethodInterface $method,
    ) {}
}
