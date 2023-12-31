<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class ValidatedRequest extends BaseAttribute
{
    /**
     * @param class-string $validator
     */
    public function __construct(public string $validator) {}
}
