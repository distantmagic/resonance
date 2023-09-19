<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use App\RPCMethod;
use Attribute;
use Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RespondsToWebSocketRPC extends BaseAttribute
{
    public function __construct(
        public RPCMethod $method,
    ) {}
}
