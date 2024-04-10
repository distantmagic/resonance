<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\SiteActionInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
final readonly class Can extends BaseAttribute
{
    public function __construct(public SiteActionInterface $siteAction) {}
}
