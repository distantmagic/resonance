<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\ContentSecurityPolicyType;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
final readonly class ContentSecurityPolicy extends BaseAttribute
{
    public function __construct(public ContentSecurityPolicyType $contentSecurityPolicyType) {}
}
