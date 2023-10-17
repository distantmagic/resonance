<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\HttpInterceptableInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Intercepts extends BaseAttribute
{
    /**
     * @param class-string<HttpInterceptableInterface> $responseClassName
     */
    public function __construct(public string $responseClassName) {}
}
