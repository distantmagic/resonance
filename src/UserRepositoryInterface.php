<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface UserRepositoryInterface
{
    public function findUserById(int|string $userId): ?UserInterface;
}
