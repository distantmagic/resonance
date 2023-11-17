<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\Entities\UserEntityInterface;

readonly class OAuth2AuthorizedUser implements UserEntityInterface
{
    public function __construct(private int|string $identifier) {}

    public function getIdentifier(): int|string
    {
        return $this->identifier;
    }
}
