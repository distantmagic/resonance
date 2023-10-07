<?php

declare(strict_types=1);

namespace Resonance;

interface UserRepositoryInterface
{
    public function findUserById(int|string $userId): ?UserInterface;
}
