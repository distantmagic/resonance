<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class OnParameterResolution extends BaseAttribute
{
    /**
     * @param non-empty-string $forwardTo
     */
    public function __construct(
        public string $forwardTo,
        public HttpControllerParameterResolutionStatus $status,
    ) {}
}
