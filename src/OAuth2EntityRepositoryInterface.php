<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * @template TAccessToken of object
 * @template TAuthCode of object
 * @template TClient of object
 * @template TRefreshToken of object
 * @template TUser of UserInterface
 */
interface OAuth2EntityRepositoryInterface
{
    /**
     * @param null|TUser $user
     * @param TClient    $client
     *
     * @return TAccessToken
     */
    public function convertAccessToken(
        EntityManagerInterface $entityManager,
        AccessTokenEntityInterface $accessTokenEntity,
        ?UserInterface $user,
        $client,
    );

    /**
     * @param null|TUser $user
     * @param TClient    $client
     *
     * @return TAuthCode
     */
    public function convertAuthCode(
        EntityManagerInterface $entityManager,
        AuthCodeEntityInterface $authCodeEntity,
        ?UserInterface $user,
        $client,
    );

    /**
     * @param TAccessToken $accessToken
     *
     * @return TRefreshToken
     */
    public function convertRefreshToken(
        EntityManagerInterface $entityManager,
        RefreshTokenEntityInterface $refreshTokenEntity,
        $accessToken,
    );

    /**
     * @return null|TAccessToken
     */
    public function findAccessToken(EntityManagerInterface $entityManager, string $token): ?object;

    /**
     * @return null|TAuthCode
     */
    public function findAuthCode(EntityManagerInterface $entityManager, string $token): ?object;

    /**
     * @return null|TClient
     */
    public function findClient(EntityManagerInterface $entityManager, string $id): ?object;

    /**
     * @return null|TRefreshToken
     */
    public function findRefreshToken(EntityManagerInterface $entityManager, string $token): ?object;

    /**
     * @return null|TUser
     */
    public function findUser(EntityManagerInterface $entityManager, int|string $id): ?UserInterface;

    /**
     * @param TAccessToken $accessToken
     */
    public function toAccessToken(
        EntityManagerInterface $entityManager,
        ClientEntityInterface $clientEntity,
        $accessToken,
    ): AccessTokenEntityInterface;

    /**
     * @param TClient $client
     */
    public function toClientEntity(
        EntityManagerInterface $entityManager,
        $client,
    ): ClientEntityInterface;
}
