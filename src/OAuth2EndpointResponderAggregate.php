<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;
use LogicException;

readonly class OAuth2EndpointResponderAggregate
{
    /**
     * @var Map<OAuth2Endpoint,HttpRouteSymbolInterface>
     */
    private Map $endpointResponderRouteSymbol;

    public function __construct()
    {
        $this->endpointResponderRouteSymbol = new Map();
    }

    public function getHttpRouteSymbolForEndpoint(OAuth2Endpoint $oAuth2Endpoint): HttpRouteSymbolInterface
    {
        $httpRouteSymbol = $this
            ->endpointResponderRouteSymbol
            ->get($oAuth2Endpoint, null)
        ;

        if (!$httpRouteSymbol) {
            throw new LogicException(sprintf(
                'There is no oauth2 endpoint responder registered for: "%s"',
                $oAuth2Endpoint->name,
            ));
        }

        return $httpRouteSymbol;
    }

    /**
     * @return Set<OAuth2Endpoint>
     */
    public function getRegisteredEndpoints(): Set
    {
        return $this->endpointResponderRouteSymbol->keys();
    }

    public function registerEndpoint(OAuth2Endpoint $oAuth2Endpoint, HttpRouteSymbolInterface $httpRouteSymbol): void
    {
        $this->endpointResponderRouteSymbol->put($oAuth2Endpoint, $httpRouteSymbol);
    }
}
