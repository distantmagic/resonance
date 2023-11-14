<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\Entities\UserEntityInterface;

interface UserInterface extends UserEntityInterface
{
    public function getIdentifier(): int|string;

    public function getRole(): UserRoleInterface;
}
