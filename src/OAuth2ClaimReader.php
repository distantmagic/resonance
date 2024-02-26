<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ProvidesAuthenticatedUser;
use Distantmagic\Resonance\Attribute\Singleton;
use Doctrine\ORM\EntityManagerInterface;
use Ds\Set;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use WeakMap;

#[GrantsFeature(Feature::OAuth2)]
#[ProvidesAuthenticatedUser(1100)]
#[Singleton(collection: SingletonCollection::AuthenticatedUserStore)]
readonly class OAuth2ClaimReader implements AuthenticatedUserStoreInterface
{
    /**
     * @var WeakMap<ServerRequestInterface,OAuth2Claim>
     */
    private WeakMap $claims;

    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
        private OAuth2EntityRepositoryInterface $oAuth2EntityRepository,
        private ResourceServer $resourceServer,
        private UserRepositoryInterface $userRepository,
        private ScopeRepositoryInterface $scopeRepository,
    ) {
        /**
         * @var WeakMap<ServerRequestInterface,OAuth2Claim>
         */
        $this->claims = new WeakMap();
    }

    public function getAuthenticatedUser(ServerRequestInterface $request): ?AuthenticatedUser
    {
        if (!$this->hasClaim($request)) {
            return null;
        }

        return new AuthenticatedUser(
            AuthenticatedUserSource::OAuth2,
            $this->readClaim($request)->user,
        );
    }

    public function hasClaim(ServerRequestInterface $request): bool
    {
        return $request->hasHeader('authorization');
    }

    public function readClaim(ServerRequestInterface $request): OAuth2Claim
    {
        if ($this->claims->offsetExists($request)) {
            return $this->claims->offsetGet($request);
        }

        $claim = $this->doReadClaim($request);

        $this->claims->offsetSet($request, $claim);

        return $claim;
    }

    private function doReadClaim(ServerRequestInterface $request): OAuth2Claim
    {
        $request = $this
            ->resourceServer
            ->validateAuthenticatedRequest($request)
        ;

        return $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($request): OAuth2Claim {
                $accessTokenId = $request->getAttribute('oauth_access_token_id');

                if (!is_string($accessTokenId)) {
                    throw OAuthServerException::invalidRequest('oauth_access_token_id');
                }

                $accessToken = $this
                    ->oAuth2EntityRepository
                    ->findAccessToken($entityManager, $accessTokenId)
                ;

                if (is_null($accessToken)) {
                    throw OAuthServerException::invalidRequest('oauth_access_token_id');
                }

                $clientId = $request->getAttribute('oauth_client_id');

                if (!is_string($clientId)) {
                    throw OAuthServerException::invalidRequest('oauth_client_id');
                }

                $client = $this
                    ->oAuth2EntityRepository
                    ->findClient($entityManager, $clientId)
                ;

                if (is_null($client)) {
                    throw OAuthServerException::invalidRequest('oauth_client_id');
                }

                $userId = $request->getAttribute('oauth_user_id');

                if (!is_string($userId)) {
                    throw OAuthServerException::invalidRequest('oauth_user_id');
                }

                $user = $this->userRepository->findUserById($userId);

                if (!$user) {
                    throw OAuthServerException::invalidRequest('oauth_user_id');
                }

                $scopes = $request->getAttribute('oauth_scopes');

                if (!is_array($scopes)) {
                    throw OAuthServerException::invalidRequest('oauth_scopes');
                }

                /**
                 * @var Set<OAuth2ScopeInterface>
                 */
                $validatedScopes = new Set();

                foreach ($scopes as $scope) {
                    if (!is_string($scope)) {
                        throw OAuthServerException::invalidScope('*not a string*');
                    }

                    $validatedScope = $this->scopeRepository->getScopeEntityByIdentifier($scope);

                    if (!$validatedScope || !($validatedScope instanceof OAuth2ScopeInterface)) {
                        throw OAuthServerException::invalidScope($scope);
                    }

                    $validatedScopes->add($validatedScope);
                }

                $clientEntity = $this->oAuth2EntityRepository->toClientEntity(
                    $entityManager,
                    $client,
                );

                $accessTokenEntity = $this->oAuth2EntityRepository->toAccessToken(
                    $entityManager,
                    $clientEntity,
                    $accessToken,
                );

                return new OAuth2Claim(
                    $accessTokenEntity,
                    $clientEntity,
                    $user,
                    $validatedScopes,
                );
            })
        ;
    }
}
