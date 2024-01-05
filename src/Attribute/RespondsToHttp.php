<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\HttpOperationId\GeneratedFromClassName;
use Distantmagic\Resonance\HttpOperationIdInterface;
use Distantmagic\Resonance\HttpRouteSymbolInterface;
use Distantmagic\Resonance\RequestMethod;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RespondsToHttp extends BaseAttribute
{
    /**
     * @param non-empty-string $pattern
     */
    public function __construct(
        public RequestMethod $method,
        public string $pattern,
        public bool $deprecated = false,
        public ?string $description = null,
        public ?HttpRouteSymbolInterface $routeSymbol = null,
        public HttpOperationIdInterface $operationId = new GeneratedFromClassName(),
        public int $priority = 0,
        public array $requirements = [],
        public ?string $summary = null,
    ) {}
}
