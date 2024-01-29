<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OAuth2Entity\Token\AccessToken;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use RuntimeException;

#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: AccessTokenRepositoryInterface::class,
)]
readonly class OAuth2AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
        private OAuth2EntityRepositoryInterface $entityRepository,
    ) {}

    /**
     * @param ScopeEntityInterface[] $scopes
     * @param mixed                  $userIdentifier explicitly mixed for typechecks
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null,
    ) {
        if (!is_null($userIdentifier) && !is_int($userIdentifier) && !is_string($userIdentifier)) {
            throw new RuntimeException('User identifier is not set');
        }

        $accessToken = new AccessToken();
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    /**
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($tokenId): bool {
                return is_null($this->entityRepository->findAccessToken($entityManager, $tokenId));
            })
        ;
    }

    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($accessTokenEntity): void {
                $client = $this->entityRepository->findClient(
                    $entityManager,
                    $accessTokenEntity->getClient()->getIdentifier(),
                );

                if (!$client) {
                    throw new RuntimeException('Client does not exist');
                }

                $userId = $accessTokenEntity->getUserIdentifier();
                $user = null;

                if (!is_null($userId)) {
                    $user = $this->entityRepository->findUser(
                        $entityManager,
                        $userId,
                    );

                    if (!$user) {
                        throw new RuntimeException('User does not exist');
                    }
                }

                $doctrineAccessToken = $this->entityRepository->convertAccessToken(
                    $entityManager,
                    $accessTokenEntity,
                    $user,
                    $client,
                );

                $entityManager->persist($doctrineAccessToken);
            })
        ;
    }

    /**
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId): void
    {
        $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($tokenId): void {
                $accessToken = $this->entityRepository->findAccessToken($entityManager, $tokenId);

                if ($accessToken) {
                    $entityManager->remove($accessToken);
                }
            })
        ;
    }
}
