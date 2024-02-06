<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ConstraintDefaultValue
{
    public function __construct(public mixed $defaultValue) {}
}
