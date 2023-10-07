<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\SiteActionInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class Can extends BaseAttribute
{
    public function __construct(public SiteActionInterface $siteAction) {}
}
