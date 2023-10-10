<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\InputValidator;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class ValidatedRequest extends BaseAttribute
{
    /**
     * @param class-string<InputValidator> $validator
     */
    public function __construct(public string $validator) {}
}
