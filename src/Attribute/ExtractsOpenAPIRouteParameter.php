<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ExtractsOpenAPIRouteParameter extends BaseAttribute
{
    /**
     * @param class-string<BaseAttribute> $fromAttribute
     */
    public function __construct(
        public string $fromAttribute,
    ) {}
}
