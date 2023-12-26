<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class OAuth2ScopePatternMatch
{
    /**
     * @var Map<string,string> $parameters
     */
    public Map $parameters;

    public function __construct()
    {
        $this->parameters = new Map();
    }
}
