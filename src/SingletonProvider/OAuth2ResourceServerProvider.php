<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OAuth2Configuration;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;

/**
 * @template-extends SingletonProvider<ResourceServer>
 */
#[Singleton(provides: ResourceServer::class)]
final readonly class OAuth2ResourceServerProvider extends SingletonProvider
{
    public function __construct(
        private AccessTokenRepositoryInterface $accessTokenRepository,
        private OAuth2Configuration $oAuth2Configuration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ResourceServer
    {
        return new ResourceServer(
            $this->accessTokenRepository,
            $this->oAuth2Configuration->jwtSigningKeyPublic,
        );
    }
}
