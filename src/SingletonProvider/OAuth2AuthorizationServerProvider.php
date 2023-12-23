<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ProvidesOAuth2Grant;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OAuth2Configuration;
use Distantmagic\Resonance\OAuth2GrantProviderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * @template-extends SingletonProvider<AuthorizationServer>
 */
#[RequiresSingletonCollection(SingletonCollection::OAuth2Grant)]
#[Singleton(provides: AuthorizationServer::class)]
final readonly class OAuth2AuthorizationServerProvider extends SingletonProvider
{
    public function __construct(
        private AccessTokenRepositoryInterface $accessTokenRepository,
        private ClientRepositoryInterface $clientRepository,
        private OAuth2Configuration $oAuth2Configuration,
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

        foreach ($this->collectGrants($singletons) as $grantAttribute) {
            $authorizationServer->enableGrantType(
                $grantAttribute->singleton->provideGrant(),
                $grantAttribute->singleton->getAccessTokenTTL(),
            );
        }

        return $authorizationServer;
    }

    /**
     * @return iterable<SingletonAttribute<OAuth2GrantProviderInterface,ProvidesOAuth2Grant>>
     */
    private function collectGrants(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            OAuth2GrantProviderInterface::class,
            ProvidesOAuth2Grant::class,
        );
    }
}
