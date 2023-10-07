<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface UserInterface
{
    public function getId(): int|string;

    public function getRole(): UserRoleInterface;
}
