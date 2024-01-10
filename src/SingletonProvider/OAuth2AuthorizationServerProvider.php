<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\OAuth2Configuration;
use Distantmagic\Resonance\OAuth2GrantCollection;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * @template-extends SingletonProvider<AuthorizationServer>
 */
#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: AuthorizationServer::class,
)]
final readonly class OAuth2AuthorizationServerProvider extends SingletonProvider
{
    public function __construct(
        private AccessTokenRepositoryInterface $accessTokenRepository,
        private ClientRepositoryInterface $clientRepository,
        private OAuth2Configuration $oAuth2Configuration,
        private OAuth2GrantCollection $oAuth2GrantCollection,
        private ScopeRepositoryInterface $scopeRepository,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): AuthorizationServer
    {
        $authorizationServer = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $this->oAuth2Configuration->jwtSigningKeyPrivate,
            $this->oAuth2Configuration->encryptionKey,
        );

        foreach ($this->oAuth2GrantCollection->oAuth2Grants as $oAuth2Grant) {
            $authorizationServer->enableGrantType(
                $oAuth2Grant->grantType,
                $oAuth2Grant->accessTokenTtl,
            );
        }

        return $authorizationServer;
    }
}
