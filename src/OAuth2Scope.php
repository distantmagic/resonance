<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

abstract readonly class OAuth2Scope implements OAuth2ScopeInterface
{
    /**
     * @param Map<string,string> $parameters
     */
    public function __construct(public Map $parameters) {}

    public function jsonSerialize(): mixed
    {
        return $this->getIdentifier();
    }
}
