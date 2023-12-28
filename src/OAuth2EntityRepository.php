<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use LogicException;

/**
 * @template-implements OAuth2EntityRepositoryInterface<
 *     object,
 *     object,
 *     object,
 *     object,
 *     UserInterface
 * >
 */
#[Singleton(provides: OAuth2EntityRepositoryInterface::class)]
readonly class OAuth2EntityRepository implements OAuth2EntityRepositoryInterface
{
    public function convertAccessToken(
        EntityManagerInterface $entityManager,
        AccessTokenEntityInterface $accessTokenEntity,
        ?UserInterface $user,
        $client,
    ): never {
        throw new LogicException('You need to provide your own OAuth2 repository');
    }

    public function convertAuthCode(
        EntityManagerInterface $entityManager,
        AuthCodeEntityInterface $authCodeEntity,
        ?UserInterface $user,
        $client,
    ): never {
        throw new LogicException('You need to provide your own OAuth2 repository');
    }

    public function convertRefreshToken(
        EntityManagerInterface $entityManager,
        RefreshTokenEntityInterface $refreshTokenEntity,
        $accessToken,
    ): never {
        throw new LogicException('You need to provide your own OAuth2 repository');
    }

    public function findAccessToken(EntityManagerInterface $entityManager, string $token): null
    {
        return null;
    }

    public function findAuthCode(EntityManagerInterface $entityManager, string $token): null
    {
        return null;
    }

    public function findClient(EntityManagerInterface $entityManager, string $id): null
    {
        return null;
    }

    public function findRefreshToken(EntityManagerInterface $entityManager, string $token): null
    {
        return null;
    }

    public function findUser(EntityManagerInterface $entityManager, int|string $id): null
    {
        return null;
    }

    public function toAccessToken(
        EntityManagerInterface $entityManager,
        ClientEntityInterface $clientEntity,
        $accessToken,
    ): never {
        throw new LogicException('You need to provide your own OAuth2 repository');
    }

    public function toClientEntity(
        EntityManagerInterface $entityManager,
        $client,
    ): never {
        throw new LogicException('You need to provide your own OAuth2 repository');
    }
}
