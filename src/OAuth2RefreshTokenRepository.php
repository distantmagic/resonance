<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OAuth2Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use RuntimeException;

#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: RefreshTokenRepositoryInterface::class,
)]
readonly class OAuth2RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
        private OAuth2EntityRepositoryInterface $entityRepository,
    ) {}

    /**
     * @return null|RefreshTokenEntityInterface
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    /**
     * @param string $tokenId
     *
     * @return bool
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($tokenId): bool {
                return is_null($this->entityRepository->findRefreshToken($entityManager, $tokenId));
            })
        ;
    }

    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($refreshTokenEntity): void {
                $accessToken = $this->entityRepository->findAccessToken(
                    $entityManager,
                    $refreshTokenEntity->getAccessToken()->getIdentifier(),
                );

                if (!$accessToken) {
                    throw new RuntimeException('Access token does not exist');
                }

                $doctrineRefreshToken = $this->entityRepository->convertRefreshToken(
                    $entityManager,
                    $refreshTokenEntity,
                    $accessToken,
                );

                $entityManager->persist($doctrineRefreshToken);
            })
        ;
    }

    /**
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId): void
    {
        $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($tokenId): void {
                $refreshToken = $this->entityRepository->findRefreshToken($entityManager, $tokenId);

                if ($refreshToken) {
                    $entityManager->remove($refreshToken);
                }
            })
        ;
    }
}
