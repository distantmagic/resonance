<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SiteActionInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class Can extends BaseAttribute
{
    /**
     * @param null|class-string<HttpResponderInterface> $onForbiddenRespondWith
     */
    public function __construct(
        public SiteActionInterface $siteAction,
        public ?string $onForbiddenRespondWith = null,
    ) {}
}
