<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class AuthenticatedUser
{
    public function __construct(
        public AuthenticatedUserSource $source,
        public UserInterface $user,
    ) {}
}
