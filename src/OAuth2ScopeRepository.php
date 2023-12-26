<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

#[Singleton(provides: ScopeRepositoryInterface::class)]
readonly class OAuth2ScopeRepository implements ScopeRepositoryInterface
{
    public function __construct(
        private OAuth2ScopeCollection $scopeCollection,
    ) {}

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string                 $grantType
     * @param null|string            $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        return $scopes;
    }

    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return null|ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        foreach ($this->scopeCollection->scopes as $attribute => $scopeClass) {
            $match = $attribute->pattern->match($identifier);

            if ($match) {
                return new $scopeClass($match->parameters);
            }
        }

        return null;
    }
}
