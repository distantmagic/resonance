<?php

declare(strict_types=1);

namespace Resonance;

interface UserRoleInterface
{
    public function isAtLeast(self $other): bool;

    public function toInt(): int;
}
