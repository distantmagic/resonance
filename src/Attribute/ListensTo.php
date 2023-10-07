<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\EventInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class ListensTo extends BaseAttribute
{
    /**
     * @param class-string<EventInterface> $eventClass
     */
    public function __construct(public string $eventClass) {}
}
