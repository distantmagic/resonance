<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use JsonSerializable;

#[Singleton]
readonly class OpenAPISchemaComponentsSecuritySchemes implements JsonSerializable
{
    public function __construct(
        private SessionConfiguration $sessionConfiguration,
        private InternalLinkBuilder $internalLinkBuilder,
        private ?OAuth2GrantCollection $oAuth2GrantCollection = null,
        private ?OAuth2EndpointResponderAggregate $oAuth2EndpointResponderAggregate = null,
        private ?OAuth2ScopeCollection $oAuth2ScopeCollection = null,
    ) {}

    public function jsonSerialize(): array
    {
        $securitySchemes = [];

        if (
            $this->oAuth2EndpointResponderAggregate
            && $this->oAuth2GrantCollection
            && $this->oAuth2ScopeCollection
        ) {
            $securitySchemes[OpenAPISecuritySchema::OAuth2->name] = new OpenAPISchemaComponentsSecuritySchemesOAuth2(
                $this->internalLinkBuilder,
                $this->oAuth2EndpointResponderAggregate,
                $this->oAuth2GrantCollection,
                $this->oAuth2ScopeCollection,
            );
        }

        $securitySchemes[OpenAPISecuritySchema::Session->name] = new OpenAPISchemaComponentsSecuritySchemesSession(
            $this->sessionConfiguration,
        );

        return $securitySchemes;
    }
}
