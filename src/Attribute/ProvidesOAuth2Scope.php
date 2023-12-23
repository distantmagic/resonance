<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ProvidesOAuth2Scope extends BaseAttribute
{
    /**
     * @param non-empty-string $separator
     */
    public function __construct(
        public string $pattern,
        public string $separator = ':',
    ) {}
}
