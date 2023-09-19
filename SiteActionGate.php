<?php

declare(strict_types=1);

namespace Resonance;

use App\DatabaseEntity\User;

abstract readonly class SiteActionGate
{
    abstract public function can(?User $user): bool;
}
