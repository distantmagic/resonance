<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

readonly class OpenAPIPathItemCollection
{
    /**
     * @var Set<OpenAPIPathItem>
     */
    public Set $pathItems;

    public function __construct()
    {
        $this->pathItems = new Set();
    }
}
