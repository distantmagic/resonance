<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;

readonly class GatekeeperRequestContext
{
    public function __construct(
        private CrudActionGateAggregate $crudActionGateAggregate,
        private AuthenticatedUserProvider $authenticatedUserProvider,
        private SiteActionGateAggregate $siteActionGateAggregate,
    ) {}

    public function getUserContext(Request $request): GatekeeperUserContext
    {
        return new GatekeeperUserContext(
            $this->crudActionGateAggregate,
            $this->siteActionGateAggregate,
            $this->authenticatedUserProvider->getAuthenticatedUser($request),
        );
    }
}
