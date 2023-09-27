<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\EventInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class ListensTo extends BaseAttribute
{
    /**
     * @param class-string<EventInterface> $eventClass
     */
    public function __construct(public string $eventClass) {}
}
