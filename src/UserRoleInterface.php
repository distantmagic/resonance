<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface UserRoleInterface
{
    public function isAtLeast(self $other): bool;

    public function toInt(): int;
}
