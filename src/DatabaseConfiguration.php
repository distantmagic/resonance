<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class DatabaseConfiguration
{
    /**
     * @var Map<non-empty-string,DatabaseConnectionPoolConfiguration>
     */
    public Map $connectionPoolConfiguration;

    public function __construct()
    {
        $this->connectionPoolConfiguration = new Map();
    }
}
