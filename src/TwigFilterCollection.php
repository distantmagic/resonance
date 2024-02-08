<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

readonly class TwigFilterCollection
{
    /**
     * @var Set<TwigFilterInterface>
     */
    public Set $twigFilters;

    public function __construct()
    {
        $this->twigFilters = new Set();
    }
}
