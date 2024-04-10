<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\HttpRouteSymbolInterface;
use Distantmagic\Resonance\RequestMethod;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
final readonly class RespondsToHttp extends BaseAttribute
{
    /**
     * @param non-empty-string $pattern
     */
    public function __construct(
        public RequestMethod $method,
        public string $pattern,
        public ?HttpRouteSymbolInterface $routeSymbol = null,
        public int $priority = 0,
        public array $requirements = [],
    ) {}
}
