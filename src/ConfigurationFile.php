<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Dflydev\DotAccessData\Data;

readonly class ConfigurationFile
{
    public function __construct(public Data $config) {}
}
