<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OAuth2Entity\Token\AuthCode;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use RuntimeException;

#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: AuthCodeRepositoryInterface::class,
)]
readonly class OAuth2AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
        private OAuth2EntityRepositoryInterface $entityRepository,
    ) {}

    /**
     * @return AuthCodeEntityInterface
     */
    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    /**
     * @param string $codeId
     *
     * @return bool
     */
    public function isAuthCodeRevoked($codeId)
    {
        return $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($codeId): bool {
                return is_null($this->entityRepository->findAuthCode($entityManager, $codeId));
            })
        ;
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($authCodeEntity): void {
                $client = $this->entityRepository->findClient(
                    $entityManager,
                    $authCodeEntity->getClient()->getIdentifier(),
                );

                if (!$client) {
                    throw new RuntimeException('Client is not set');
                }

                $userId = $authCodeEntity->getUserIdentifier();
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

                $doctrineAuthCode = $this->entityRepository->convertAuthCode(
                    $entityManager,
                    $authCodeEntity,
                    $user,
                    $client,
                );

                $entityManager->persist($doctrineAuthCode);
            })
        ;
    }

    /**
     * @param string $codeId
     */
    public function revokeAuthCode($codeId): void
    {
        $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($codeId): void {
                $accessToken = $this->entityRepository->findAuthCode($entityManager, $codeId);

                if ($accessToken) {
                    $entityManager->remove($accessToken);
                }
            })
        ;
    }
}
