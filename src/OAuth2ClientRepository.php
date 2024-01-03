<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: ClientRepositoryInterface::class,
)]
readonly class OAuth2ClientRepository implements ClientRepositoryInterface
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
        private OAuth2EntityRepositoryInterface $entityRepository,
    ) {}

    /**
     * @param string $clientIdentifier
     *
     * @return null|ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier)
    {
        return $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($clientIdentifier) {
                $doctrineClient = $this->entityRepository->findClient(
                    $entityManager,
                    $clientIdentifier,
                );

                if (!$doctrineClient) {
                    return null;
                }

                return $this->entityRepository->toClientEntity($entityManager, $doctrineClient);
            })
        ;
    }

    /**
     * @param string      $clientIdentifier
     * @param null|string $clientSecret
     * @param null|string $grantType
     *
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $client = $this->getClientEntity($clientIdentifier);

        if (is_null($client)) {
            return false;
        }

        if (($client instanceof OAuth2SecretAwareClient)) {
            if (!$clientSecret) {
                return $client->isSecretRequired();
            }

            if (!$client->isSecretValid($clientSecret)) {
                return false;
            }
        }

        if (($client instanceof OAuth2GrantAwareClient)) {
            if (!$grantType) {
                return $client->isGrantTypeRequired();
            }

            if (!$client->isGrantTypeAccepted($grantType)) {
                return false;
            }
        }

        return true;
    }
}
