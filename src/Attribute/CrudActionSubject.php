<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class CrudActionSubject extends BaseAttribute
{
    /**
     * @param class-string $gate
     */
    public function __construct(public string $gate) {}
}
