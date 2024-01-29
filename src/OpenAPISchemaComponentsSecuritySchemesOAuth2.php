<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OpenAPISchemaComponentsSecuritySchemesOAuth2 implements JsonSerializable
{
    public function __construct(
        private InternalLinkBuilder $internalLinkBuilder,
        private OAuth2EndpointResponderAggregate $oAuth2EndpointResponderAggregate,
        private OAuth2GrantCollection $oAuth2GrantCollection,
        private OAuth2ScopeCollection $oAuth2ScopeCollection,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'type' => 'oauth2',
            'flows' => $this->serializeGrants($this->serializeGrant(
                $this->buildEndpointUrl(OAuth2Endpoint::AccessToken),
                $this->buildEndpointUrl(OAuth2Endpoint::Authorization),
                $this->serializeScopes(),
            )),
        ];
    }

    private function buildEndpointUrl(OAuth2Endpoint $oAuth2Endpoint): string
    {
        $httpRouteSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->getHttpRouteSymbolForEndpoint($oAuth2Endpoint)
        ;

        return $this->internalLinkBuilder->buildUrl($httpRouteSymbol);
    }

    private function serializeGrant(
        string $accessTokenUrl,
        string $authorizationUrl,
        array $serializedScopes,
    ): array {
        return [
            'tokenUrl' => $accessTokenUrl,
            'authorizationUrl' => $authorizationUrl,
            'refreshUrl' => $accessTokenUrl,
            'scopes' => $serializedScopes,
        ];
    }

    private function serializeGrants(array $serializedFlow): array
    {
        $flows = [];

        foreach ($this->oAuth2GrantCollection->oAuth2Grants as $oAuth2Grant) {
            $grantIdentifier = $oAuth2Grant->grantType->getIdentifier();
            $openAPIGrantIdentifier = match ($grantIdentifier) {
                'authorization_code' => 'authorizationCode',
                'client_credentials' => 'clientCredentials',
                'refresh_token' => null,
                default => $grantIdentifier,
            };

            if (is_string($openAPIGrantIdentifier)) {
                $flows[$openAPIGrantIdentifier] = $serializedFlow;
            }
        }

        return $flows;
    }

    private function serializeScopes(): array
    {
        $serializedScopes = [];

        foreach ($this->oAuth2ScopeCollection->scopes->keys() as $scope) {
            $serializedScopes[$scope->pattern->pattern] = $scope->description;
        }

        return $serializedScopes;
    }
}
