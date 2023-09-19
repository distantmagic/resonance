<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use App\SiteAction;
use Attribute;
use Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class DecidesSiteAction extends BaseAttribute
{
    public function __construct(
        public SiteAction $siteAction,
    ) {}
}
