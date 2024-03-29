<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class SiteActionGate
{
    abstract public function can(?AuthenticatedUser $authenticatedUser): bool;

    public function requiresAuthenticatedUser(): bool
    {
        return true;
    }
}
