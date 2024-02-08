<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

readonly class TwigFunctionCollection
{
    /**
     * @var Set<TwigFunctionInterface>
     */
    public Set $twigFunctions;

    public function __construct()
    {
        $this->twigFunctions = new Set();
    }
}
