<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;

interface UserRepositoryInterface
{
    public function findUserById(Request $request, int|string $userId): ?UserInterface;
}
