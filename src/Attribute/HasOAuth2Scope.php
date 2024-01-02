<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\OAuth2ScopePattern;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class HasOAuth2Scope extends BaseAttribute
{
    public OAuth2ScopePattern $pattern;

    /**
     * @param non-empty-string $pattern
     * @param non-empty-string $separator
     */
    public function __construct(
        string $pattern,
        string $separator = ':',
    ) {
        $this->pattern = new OAuth2ScopePattern($pattern, $separator);
    }
}
