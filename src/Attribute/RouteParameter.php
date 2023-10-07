<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\CrudAction;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class RouteParameter extends BaseAttribute
{
    public function __construct(
        public string $from,
        public CrudAction $intent = CrudAction::Read,
    ) {}
}
