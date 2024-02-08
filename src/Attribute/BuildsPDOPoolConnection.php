<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class BuildsPDOPoolConnection extends BaseAttribute
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
    ) {}
}
