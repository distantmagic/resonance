<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class RedisConfiguration
{
    /**
     * @var Map<string,RedisConnectionPoolConfiguration>
     */
    public Map $connectionPoolConfiguration;

    public function __construct()
    {
        $this->connectionPoolConfiguration = new Map();
    }
}
