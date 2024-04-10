<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\OAuth2Endpoint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
final readonly class RespondsToOAuth2Endpoint extends BaseAttribute
{
    public function __construct(
        public OAuth2Endpoint $endpoint,
    ) {}
}
