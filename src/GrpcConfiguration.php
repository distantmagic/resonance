<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class GrpcConfiguration
{
    /**
     * @var Map<non-empty-string,GrpcPoolConfiguration>
     */
    public Map $poolConfiguration;

    public function __construct()
    {
        $this->poolConfiguration = new Map();
    }
}
