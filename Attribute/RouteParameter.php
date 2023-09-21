<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\CrudAction;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class RouteParameter extends BaseAttribute
{
    public function __construct(
        public string $from,
        public CrudAction $intent = CrudAction::Read,
    ) {}
}
