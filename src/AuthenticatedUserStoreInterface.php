<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;

interface AuthenticatedUserStoreInterface
{
    public function getAuthenticatedUser(ServerRequestInterface $request): ?AuthenticatedUser;
}
