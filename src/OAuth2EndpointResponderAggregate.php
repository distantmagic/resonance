<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class OAuth2EndpointResponderAggregate
{
    /**
     * @var Map<OAuth2Endpoint,HttpRouteSymbolInterface>
     */
    public Map $endpointResponderRouteSymbol;

    public function __construct()
    {
        $this->endpointResponderRouteSymbol = new Map();
    }
}
