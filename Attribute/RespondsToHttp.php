<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\HttpRouteSymbolInterface;
use Resonance\RequestMethod;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RespondsToHttp extends BaseAttribute
{
    public function __construct(
        public RequestMethod $method,
        public string $pattern,
        public HttpRouteSymbolInterface $routeSymbol,
    ) {}
}
