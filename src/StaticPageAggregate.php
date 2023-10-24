<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class StaticPageAggregate
{
    /**
     * @var Map<string, StaticPage>
     */
    public Map $staticPages;

    public function __construct()
    {
        $this->staticPages = new Map();
    }
}
