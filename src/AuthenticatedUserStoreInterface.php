<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;

interface AuthenticatedUserStoreInterface
{
    public function getAuthenticatedUser(Request $request): ?AuthenticatedUser;
}
