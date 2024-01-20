<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use Swoole\Http\Request;

readonly class AuthenticatedUserStoreAggregate
{
    /**
     * @var Set<AuthenticatedUserStoreInterface>
     */
    public Set $storages;

    public function __construct()
    {
        /**
         * @var Set<AuthenticatedUserStoreInterface>
         */
        $this->storages = new Set();
    }

    public function getAuthenticatedUser(Request $request): ?AuthenticatedUser
    {
        foreach ($this->storages as $storage) {
            $authenticatedUser = $storage->getAuthenticatedUser($request);

            if ($authenticatedUser) {
                return $authenticatedUser;
            }
        }

        return null;
    }
}
