<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;

readonly class OAuth2Claim
{
    /**
     * @param Set<OAuth2ScopeInterface> $scopes
     */
    public function __construct(
        public AccessTokenEntityInterface $accessToken,
        public ClientEntityInterface $client,
        public UserInterface $user,
        public Set $scopes,
    ) {}
}
