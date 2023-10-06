<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;

#[Singleton]
readonly class TwigFunctionRoute
{
    public function __invoke() {}
}
