<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\Entities\UserEntityInterface;
use Stringable;

interface UserInterface extends UserEntityInterface
{
    public function getIdentifier(): int|string|Stringable;

    public function getRole(): UserRoleInterface;
}
