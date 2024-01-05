<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Set;
use Generator;
use LogicException;
use RuntimeException;

#[Singleton]
readonly class OpenAPISchemaBuilder
{
    public const OAUTH2_SECURITY_KEY = 'oauth2';

    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private InternalLinkBuilder $internalLinkBuilder,
        private OpenAPICollectionAggregate $openAPICollectionAggregate,
        private OpenAPIConfiguration $openAPIConfiguration,
        private ?OAuth2EndpointResponderAggregate $oAuth2EndpointResponderAggregate = null,
        private ?OAuth2GrantCollection $oAuth2GrantCollection = null,
        private ?OAuth2ScopeCollection $oAuth2ScopeCollection = null,
    ) {}

    public function buildCollection(OpenAPICollectionSymbolInterface $collectionSymbol): array
    {
        /**
         * @var Set<string> $operationIds
         */
        $operationIds = new Set();

        return [
            'openapi' => '3.0.0',
            'info' => [
                'description' => $this->openAPIConfiguration->description,
                'title' => $this->openAPIConfiguration->title,
            ],
            'servers' => [
                'url' => $this->applicationConfiguration->url,
            ],
            'components' => $this->buildComponentsScheme(),
            'paths' => iterator_to_array($this->responderAttributesToPaths($collectionSymbol, $operationIds)),
        ];
    }

    private function buildComponentsScheme(): array
    {
        return [
            'securitySchemes' => $this->buildSecuritySchemes(),
        ];
    }

    private function buildOAuth2Url(OAuth2Endpoint $oAuth2Endpoint): string
    {
        if (!$this->oAuth2EndpointResponderAggregate) {
            throw new LogicException('Enable "OAuth2" feature to build OAuth2 urls in OpenAPI schema');
        }

        $routeSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->endpointResponderRouteSymbol
            ->get($oAuth2Endpoint)
        ;

        return sprintf(
            '%s%s',
            $this->applicationConfiguration->url,
            $this->internalLinkBuilder->build($routeSymbol),
        );
    }

    private function buildSecuritySchemes(): array
    {
        if (!isset(
            $this->oAuth2EndpointResponderAggregate,
            $this->oAuth2GrantCollection,
            $this->oAuth2ScopeCollection,
        )) {
            return [];
        }

        $oauth2Grants = [];
        $accessTokenUrl = $this->buildOAuth2Url(OAuth2Endpoint::AccessToken);
        $authorizationUrl = $this->buildOAuth2Url(OAuth2Endpoint::Authorization);

        $scopes = [];

        foreach ($this->oAuth2ScopeCollection->scopes->keys() as $providesOAuth2Scope) {
            $scopes[$providesOAuth2Scope->pattern->pattern] = $providesOAuth2Scope->description;
        }

        foreach ($this->oAuth2GrantCollection->oauth2Grants as $oAuth2Grant) {
            $grantIdentifier = $oAuth2Grant->grantType->getIdentifier();

            $openApiGrantName = match ($grantIdentifier) {
                'authorization_code' => 'authorizationCode',
                'client_credentials' => 'clientCredentials',
                'implicit' => 'implicit',
                'password' => 'password',
                default => null,
            };

            if ($openApiGrantName) {
                $oauth2Grants[$openApiGrantName] = match ($grantIdentifier) {
                    'authorization_code' => [
                        'authorizationUrl' => $authorizationUrl,
                        'refreshUrl' => $accessTokenUrl,
                        'tokenUrl' => $accessTokenUrl,
                        'scopes' => $scopes,
                    ],
                    'client_credentials' => [
                        'refreshUrl' => $accessTokenUrl,
                        'tokenUrl' => $accessTokenUrl,
                        'scopes' => $scopes,
                    ],
                    'implicit' => [
                        'authorizationUrl' => $authorizationUrl,
                        'refreshUrl' => $accessTokenUrl,
                        'scopes' => $scopes,
                    ],
                };
            }
        }

        return [
            self::OAUTH2_SECURITY_KEY => [
                'type' => 'oauth2',
                'flows' => $oauth2Grants,
            ],
        ];
    }

    /**
     * @param Set<string> $operationIds
     */
    private function collectionEndpointToPathDefinition(
        OpenAPICollectionSymbolInterface $collectionSymbol,
        OpenAPICollectionEndpoint $openAPICollectionEndpoint,
        Set $operationIds,
    ): array {
        $pathMetadata = [];

        $operationId = $openAPICollectionEndpoint
            ->respondsToHttp
            ->operationId
            ->generateOperationId(
                $openAPICollectionEndpoint->respondsToHttp->method,
                $openAPICollectionEndpoint->httpResponderReflectionClass,
            )
        ;

        if ($operationIds->contains($operationId)) {
            throw new RuntimeException(sprintf(
                'HTTP operation id "%s" is not unique in collection "%s", responder: "%s"',
                $operationId,
                $collectionSymbol->getName(),
                $openAPICollectionEndpoint->httpResponderReflectionClass->getName(),
            ));
        }

        $operationIds->add($operationId);

        $pathMetadata['deprecated'] = $openAPICollectionEndpoint->respondsToHttp->deprecated;

        if (is_string($openAPICollectionEndpoint->respondsToHttp->description)) {
            $pathMetadata['description'] = $openAPICollectionEndpoint->respondsToHttp->description;
        }

        $pathMetadata['operationId'] = $operationId;

        if (is_string($openAPICollectionEndpoint->respondsToHttp->summary)) {
            $pathMetadata['summary'] = $openAPICollectionEndpoint->respondsToHttp->summary;
        }

        $pathMetadata['security'] = [];

        if (!$openAPICollectionEndpoint->requiredOAuth2Scopes->isEmpty()) {
            $requiredScopes = [];

            foreach ($openAPICollectionEndpoint->requiredOAuth2Scopes as $requiresOAuth2Scope) {
                $requiredScopes[] = $requiresOAuth2Scope->pattern->pattern;
            }

            $pathMetadata['security'][] = [
                self::OAUTH2_SECURITY_KEY => $requiredScopes,
            ];
        }

        return [
            strtolower($openAPICollectionEndpoint->respondsToHttp->method->value) => $pathMetadata,
        ];
    }

    /**
     * @param Set<string> $operationIds
     */
    private function responderAttributesToPaths(
        OpenAPICollectionSymbolInterface $collectionSymbol,
        Set $operationIds,
    ): Generator {
        $endpointsCollection = $this
            ->openAPICollectionAggregate
            ->openAPICollections
            ->get($collectionSymbol, null)
        ;

        if (is_null($endpointsCollection)) {
            throw new RuntimeException(sprintf(
                'OpenAPI endpoints collection is not registered: %s',
                $collectionSymbol->getName(),
            ));
        }

        foreach ($endpointsCollection as $openAPICollectionEndpoint) {
            yield $openAPICollectionEndpoint->respondsToHttp->pattern => $this->collectionEndpointToPathDefinition(
                $collectionSymbol,
                $openAPICollectionEndpoint,
                $operationIds,
            );
        }
    }
}
