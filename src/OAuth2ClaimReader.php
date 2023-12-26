<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Doctrine\ORM\EntityManagerInterface;
use Ds\Set;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
readonly class OAuth2ClaimReader
{
    /**
     * @var WeakMap<Request,OAuth2Claim>
     */
    private WeakMap $claims;

    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
        private OAuth2EntityRepositoryInterface $oAuth2EntityRepository,
        private PsrServerRequestConverter $psrServerRequestConverter,
        private ResourceServer $resourceServer,
        private UserRepositoryInterface $userRepository,
        private ScopeRepositoryInterface $scopeRepository,
    ) {
        /**
         * @var WeakMap<Request,OAuth2Claim>
         */
        $this->claims = new WeakMap();
    }

    public function hasClaim(Request $request): bool
    {
        return isset($request->header['authorization']) && is_string($request->header['authorization']);
    }

    public function readClaim(Request $request): OAuth2Claim
    {
        if ($this->claims->offsetExists($request)) {
            return $this->claims->offsetGet($request);
        }

        $claim = $this->doReadClaim($request);

        $this->claims->offsetSet($request, $claim);

        return $claim;
    }

    private function doReadClaim(Request $request): OAuth2Claim
    {
        $serverRequest = $this->psrServerRequestConverter->convertToServerRequest($request);
        $serverRequest = $this
            ->resourceServer
            ->validateAuthenticatedRequest($serverRequest)
        ;

        return $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($request, $serverRequest) {
                $accessTokenId = $serverRequest->getAttribute('oauth_access_token_id');

                if (!is_string($accessTokenId)) {
                    throw OAuthServerException::invalidRequest('oauth_access_token_id');
                }

                $accessToken = $this
                    ->oAuth2EntityRepository
                    ->findAccessToken($entityManager, $accessTokenId)
                ;

                if (!($accessToken instanceof AccessTokenEntityInterface)) {
                    throw OAuthServerException::invalidRequest('oauth_access_token_id');
                }

                $clientId = $serverRequest->getAttribute('oauth_client_id');

                if (!is_string($clientId)) {
                    throw OAuthServerException::invalidRequest('oauth_client_id');
                }

                $client = $this
                    ->oAuth2EntityRepository
                    ->findClient($entityManager, $clientId)
                ;

                if (!($client instanceof ClientEntityInterface)) {
                    throw OAuthServerException::invalidRequest('oauth_client_id');
                }

                $userId = $serverRequest->getAttribute('oauth_user_id');

                if (!is_string($userId)) {
                    throw OAuthServerException::invalidRequest('oauth_user_id');
                }

                $user = $this
                    ->userRepository
                    ->findUserById($request, $userId)
                ;

                if (!$user) {
                    throw OAuthServerException::invalidRequest('oauth_user_id');
                }

                $scopes = $serverRequest->getAttribute('oauth_scopes');

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

                return new OAuth2Claim(
                    $accessToken,
                    $client,
                    $user,
                    $validatedScopes,
                );
            })
        ;
    }
}
